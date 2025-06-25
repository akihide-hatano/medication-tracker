<?php

namespace App\Http\Controllers;

use App\Models\TimingTag; // TimingTag モデルをインポート
use Illuminate\Http\Request; // Request クラスをインポート
use Illuminate\Support\Facades\Log; // デバッグ用にLogをインポート（任意）

class TimingTagController extends Controller
{
    /**
     * 服用タイミングの一覧ページを表示
     * GET /timing_tags
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $timingTags = TimingTag::all();
        return view('timing_tags.index', compact('timingTags'));
    }

    /**
     * 新しい服用タイミングの作成フォームを表示
     * GET /timing_tags/create
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('timing_tags.create'); // 新しいタイミングの作成フォームを表示するビュー
    }

    /**
     * 新しい服用タイミングをデータベースに保存
     * POST /timing_tags
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 入力値のバリデーション
        $validatedData = $request->validate([
            'timing_name' => 'required|string|max:255|unique:timing_tags,timing_name', // timing_nameは必須かつユニーク
        ]);

        // データベースに服用タイミングを作成
        $timingTag = TimingTag::create($validatedData);

        // 成功メッセージと共に一覧ページにリダイレクト
        return redirect()->route('timing_tags.index')->with('success', '服用タイミングが正常に追加されました！');
    }

    /**
     * 特定の服用タイミングの詳細を表示
     * GET /timing_tags/{timing_tag}
     *
     * @param  \App\Models\TimingTag  $timingTag （ルートモデルバインディングにより自動注入）
     * @return \Illuminate\Contracts\View\View
     */
    public function show(TimingTag $timingTag)
    {
        return view('timing_tags.show', compact('timingTag')); // 詳細を表示するビュー
    }

    /**
     * 特定の服用タイミングの編集フォームを表示
     * GET /timing_tags/{timing_tag}/edit
     *
     * @param  \App\Models\TimingTag  $timingTag
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(TimingTag $timingTag)
    {
        return view('timing_tags.edit', compact('timingTag')); // 編集フォームを表示するビュー
    }

    /**
     * 特定の服用タイミングをデータベースで更新
     * PUT/PATCH /timing_tags/{timing_tag}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TimingTag  $timingTag
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TimingTag $timingTag)
    {
        // 入力値のバリデーション
        // 更新時もtiming_nameはユニークにするが、自分自身のレコードは除く
        $validatedData = $request->validate([
            'timing_name' => 'required|string|max:255|unique:timing_tags,timing_name,' . $timingTag->timing_tag_id . ',timing_tag_id',
        ]);

        // 服用タイミングの情報を更新
        $timingTag->update($validatedData);

        // 成功メッセージと共に詳細ページにリダイレクト
        return redirect()->route('timing_tags.show', $timingTag->timing_tag_id)->with('success', '服用タイミングが正常に更新されました！');
    }

    /**
     * 特定の服用タイミングをデータベースから削除
     * DELETE /timing_tags/{timing_tag}
     *
     * @param  \App\Models\TimingTag  $timingTag
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TimingTag $timingTag)
    {
        $timingTag->delete();

        // 成功メッセージと共に一覧ページにリダイレクト
        return redirect()->route('timing_tags.index')->with('success', '服用タイミングが正常に削除されました！');
    }
}