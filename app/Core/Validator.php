<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Basit kural tabanlı doğrulayıcı.
 * Örnek: Validator::make($data, ['email' => 'required|email|max:190'], ['email' => 'E-posta'])
 */
final class Validator
{
    private array $errors = [];

    private function __construct(private array $data, private array $rules, private array $labels)
    {
    }

    public static function make(array $data, array $rules, array $labels = []): self
    {
        $v = new self($data, $rules, $labels);
        $v->run();
        return $v;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $ruleSet) {
            $rules = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
            $value = $this->data[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $label = $this->labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
            $isEmpty = $value === null || $value === '' || $value === [];

            if (!in_array('required', $rules, true) && $isEmpty) {
                continue;
            }

            foreach ($rules as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                $error = $this->check($name, $arg, $value, $label, $isEmpty);
                if ($error !== null) {
                    $this->errors[$field] = $error;
                    break;
                }
            }
        }
    }

    private function check(string $rule, ?string $arg, mixed $value, string $label, bool $isEmpty): ?string
    {
        $str = is_scalar($value) ? (string) $value : '';
        switch ($rule) {
            case 'required':
                return $isEmpty ? "$label alanı zorunludur." : null;
            case 'email':
                return filter_var($str, FILTER_VALIDATE_EMAIL) ? null : "$label geçerli bir e-posta adresi olmalıdır.";
            case 'min':
                return mb_strlen($str) >= (int) $arg ? null : "$label en az $arg karakter olmalıdır.";
            case 'max':
                return mb_strlen($str) <= (int) $arg ? null : "$label en fazla $arg karakter olabilir.";
            case 'numeric':
                return is_numeric(str_replace(',', '.', $str)) ? null : "$label sayısal bir değer olmalıdır.";
            case 'integer':
                return filter_var($str, FILTER_VALIDATE_INT) !== false ? null : "$label tam sayı olmalıdır.";
            case 'min_value':
                return (float) str_replace(',', '.', $str) >= (float) $arg ? null : "$label en az $arg olmalıdır.";
            case 'max_value':
                return (float) str_replace(',', '.', $str) <= (float) $arg ? null : "$label en fazla $arg olabilir.";
            case 'in':
                return in_array($str, explode(',', (string) $arg), true) ? null : "$label için geçersiz bir seçim yapıldı.";
            case 'same':
                return $str === (string) ($this->data[$arg] ?? '') ? null : "$label alanı eşleşmiyor.";
            case 'phone':
                $digits = preg_replace('/\D+/', '', $str);
                return strlen((string) $digits) >= 10 && strlen((string) $digits) <= 13 ? null : "$label geçerli bir telefon numarası olmalıdır.";
            case 'date':
                return strtotime($str) !== false ? null : "$label geçerli bir tarih olmalıdır.";
            case 'color':
                return preg_match('/^#[0-9a-fA-F]{6}$/', $str) ? null : "$label geçerli bir renk kodu olmalıdır (#RRGGBB).";
            case 'slug':
                return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $str) ? null : "$label yalnızca küçük harf, rakam ve tire içerebilir.";
            case 'unique':
                // unique:tablo,kolon,haricId
                [$table, $column, $except] = array_pad(explode(',', (string) $arg), 3, null);
                $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
                $params = [$str];
                if ($except) {
                    $sql .= ' AND id <> ?';
                    $params[] = (int) $except;
                }
                return (int) Database::value($sql, $params) === 0 ? null : "Bu $label zaten kullanılıyor.";
            case 'password':
                return preg_match('/[A-Za-zÇĞİÖŞÜçğıöşü]/u', $str) && preg_match('/\d/', $str)
                    ? null : "$label en az bir harf ve bir rakam içermelidir.";
        }
        return null;
    }
}
