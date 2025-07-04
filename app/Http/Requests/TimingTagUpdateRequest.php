// app/Http/Requests/TimingTagUpdateRequest.php

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimingTagUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // コントローラーでPolicyによる認可を行うため、ここではtrueで問題ありません。
        // もしフォームリクエストで認可を完結させたい場合は、ここでロジックを記述します。
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // unique ルールで更新対象のIDを除外することを忘れないでください。
        // ルートモデルバインディングで渡されるモデルの主キー名が 'timing_tag_id' であると仮定します。
        // 例: /timing_tags/{timing_tag} の {timing_tag} が TimingTag モデルにバインドされる場合、
        // route('timing_tag') は TimingTag モデルインスタンスになります。
        $timingTagId = $this->route('timing_tag')->timing_tag_id;

        return [
            'timing_name' => [
                'required',
                'string',
                'max:255',
                'unique:timing_tags,timing_name,' . $timingTagId . ',timing_tag_id',
            ],
        ];
    }
}