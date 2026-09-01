<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\StoreContactRequest as BaseStoreContactRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateContactRequest extends BaseStoreContactRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * リクエストの認可
     */
    public function authorize(): bool
    {
        return true; // 認証不要の仕様のため true に設定
    }

    /**
     * Get the validation rules that apply to the request.
     * バリデーションルール
     * 親（StoreContactRequest）と同じ。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 親（Web用）のルールを取得（そのまま適用）
        return parent::rules();

    }
}
