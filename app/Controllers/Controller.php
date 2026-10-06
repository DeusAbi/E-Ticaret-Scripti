<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;

abstract class Controller
{
    /**
     * Doğrulama başarısızsa hataları flash'layıp geri yönlendirir.
     * Başarılıysa kırpılmış verileri döner.
     */
    protected function validate(array $rules, array $labels = [], ?string $redirectTo = null): array
    {
        $data = array_map(static fn ($v) => is_string($v) ? trim($v) : $v, $_POST);
        $v = Validator::make($data, $rules, $labels);
        if ($v->fails()) {
            if (is_ajax()) {
                json_response(['ok' => false, 'errors' => $v->errors(), 'message' => reset($v->errors())], 422);
            }
            with_errors($v->errors());
            flash('error', 'Lütfen formdaki hataları düzeltin.');
            $redirectTo ? redirect($redirectTo) : back();
        }
        $out = [];
        foreach (array_keys($rules) as $field) {
            $out[$field] = $data[$field] ?? null;
        }
        return $out;
    }
}
