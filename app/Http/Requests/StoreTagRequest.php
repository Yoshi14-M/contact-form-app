<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * リクエストの認可
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * バリデーションルール
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:tags,name'],
        ];
    }
    /** バリエーションメッセージ */
    public function messages(): array
    {
        return [
            'name.repuired' => 'タグ名を入力してください',
            'name.max' => 'タグ名は50文字以内で入力してください',
            'name.unique' => 'そのタグ名は既に使用されています',
        ];
    }
}
