<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\IndexContactRequest as BaseIndexContactRequest;

class IndexContactRequest extends BaseIndexContactRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * リクエストの認可
     */
    public function authorize(): bool
    {
        return true; // 認証不要の仕様のため true に設定
    }

    public function rules(): array
    {
        // 親（Web用）のルールを取得
        $rules = parent::rules();

        // API用の仕様に合わせて書き換える・追加する
        $rules['gender'] = ['nullable', 'integer', 'in:1,2,3']; // 0を除外
        $rules['per_page'] = ['nullable', 'integer', 'min:1', 'max:100']; // 追加
        $rules['page'] = ['nullable', 'integer', 'min:1']; // 追加

        return $rules;
    }
}
