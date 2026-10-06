<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

/**
 * E-posta gönderimi: "log", "mail" veya "smtp" sürücüsü.
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $html): bool
    {
        $driver = (string) config('mail.driver', 'log');
        try {
            return match ($driver) {
                'smtp'  => self::sendSmtp($to, $subject, $html),
                'mail'  => self::sendMail($to, $subject, $html),
                default => self::log($to, $subject, $html),
            };
        } catch (Throwable $e) {
            error_log('[Mailer] ' . $e->getMessage());
            self::log($to, $subject, $html, 'GÖNDERİLEMEDİ: ' . $e->getMessage());
            return false;
        }
    }

    /** Ortak e-posta şablonu ile gönderir */
    public static function sendTemplate(string $to, string $subject, string $title, string $bodyHtml): bool
    {
        $html = View::partial('partials/email', ['title' => $title, 'body' => $bodyHtml]);
        return self::send($to, $subject, $html);
    }

    private static function fromHeader(): string
    {
        $name = (string) config('mail.from_name', setting('store_name', 'Mağaza'));
        return '=?UTF-8?B?' . base64_encode($name) . '?= <' . config('mail.from') . '>';
    }

    private static function log(string $to, string $subject, string $html, string $note = ''): bool
    {
        $entry = sprintf(
            "[%s] %s\nKime: %s\nKonu: %s\n%s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $note,
            $to,
            $subject,
            preg_replace("/\n\s*\n+/", "\n", trim(html_entity_decode(strip_tags(preg_replace("/<(br|\/p|\/tr|\/h\d)>/i", "\n", $html))))),
            str_repeat('-', 60)
        );
        file_put_contents(STORAGE_PATH . '/logs/mail.log', $entry, FILE_APPEND | LOCK_EX);
        return true;
    }

    private static function sendMail(string $to, string $subject, string $html): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . self::fromHeader(),
        ];
        return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, implode("\r\n", $headers));
    }

    private static function sendSmtp(string $to, string $subject, string $html): bool
    {
        $host = (string) config('mail.host');
        $port = (int) config('mail.port', 587);
        $enc = (string) config('mail.encryption', 'tls');

        $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $socket = @stream_socket_client($remote, $errno, $errstr, 15);
        if (!$socket) {
            throw new RuntimeException("SMTP bağlantısı kurulamadı: $errstr");
        }
        stream_set_timeout($socket, 15);

        $read = static function () use ($socket): string {
            $data = '';
            while (($line = fgets($socket, 515)) !== false) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $data;
        };
        $cmd = static function (string $command, array $expect) use ($socket, $read): string {
            fwrite($socket, $command . "\r\n");
            $response = $read();
            if (!in_array((int) substr($response, 0, 3), $expect, true)) {
                throw new RuntimeException('SMTP hatası: ' . trim($response));
            }
            return $response;
        };

        $read();
        $ehloHost = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $cmd("EHLO $ehloHost", [250]);
        if ($enc === 'tls') {
            $cmd('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('TLS başlatılamadı.');
            }
            $cmd("EHLO $ehloHost", [250]);
        }
        if (config('mail.username')) {
            $cmd('AUTH LOGIN', [334]);
            $cmd(base64_encode((string) config('mail.username')), [334]);
            $cmd(base64_encode((string) config('mail.password')), [235]);
        }
        $cmd('MAIL FROM:<' . config('mail.from') . '>', [250]);
        $cmd("RCPT TO:<$to>", [250, 251]);
        $cmd('DATA', [354]);

        $headers = implode("\r\n", [
            'Date: ' . date('r'),
            'From: ' . self::fromHeader(),
            "To: <$to>",
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ]);
        $body = chunk_split(base64_encode($html));
        $cmd($headers . "\r\n\r\n" . $body . "\r\n.", [250]);
        $cmd('QUIT', [221]);
        fclose($socket);
        return true;
    }
}
