<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Auth を使う場合は追加

class PostUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * このリクエストを行う権限がユーザーにあるかを判断します。
     */
    public function authorize(): bool
    {
        // 投稿の所有者のみが更新できることを許可
        // ルートモデルバインディングで渡されたPostインスタンスにアクセス
        $post = $this->route('post'); // ルートパラメータの名前が'post'であることを想定

        // ログインユーザーが投稿の所有者であるかチェック
        return $post && $post->user_id === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     * リクエストに適用されるバリデーションルールを取得します。
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'post_date' => ['required', 'date'],
            'content' => ['nullable', 'string', 'max:1000'],
            'all_meds_taken' => ['required', 'boolean'],
            // all_meds_taken が false (0) の場合に reason_not_taken が必須
            'reason_not_taken' => ['nullable', 'string', 'max:500', 'required_if:all_meds_taken,0'],
            'medications' => ['required', 'array', 'min:1'], // 少なくとも1つの薬の記録が必要
            'medications.*.medication_id' => ['required', 'exists:medications,medication_id'],
            'medications.*.timing_tag_id' => ['required', 'exists:timing_tags,timing_tag_id'],
            'medications.*.is_completed' => ['required', 'boolean'],
            'medications.*.taken_dosage' => 'nullable|string|max:255',
            'medications.*.reason_not_taken' => 'nullable|string|max:500', // 個別の服用記録の理由
        ];
    }

    /**
     * Get custom messages for validator errors.
     * バリデーターエラーのためのカスタムメッセージを取得します。
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'medications.required' => '少なくとも1つの薬の記録が必要です。',
            'medications.min' => '少なくとも1つの薬の記録が必要です。',
            'medications.*.medication_id.required' => '薬は必須です。',
            'medications.*.medication_id.exists' => '選択された薬は無効です。',
            'medications.*.timing_tag_id.required' => '服用タイミングは必須です。',
            'medications.*.timing_tag_id.exists' => '選択された服用タイミングは無効です。',
            'medications.*.is_completed.required' => '服薬状況は必須です。',
            'medications.*.is_completed.boolean' => '服薬状況は真偽値でなければなりません。',
            'reason_not_taken.required_if' => '全ての薬を服用しなかった場合、その理由を記入してください。',
        ];
    }
}