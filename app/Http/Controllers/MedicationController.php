<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Log も引き続き利用可能

class MedicationController extends Controller
{

    public function index()
    {
        $medications = Medication::all();
        dump($medications); // ★追加: データベースから取得した薬のコレクションを確認
        return view('medications.index', compact('medications'));
    }

    public function create()
    {
        return view('medications.create');
    }

    /**
     * 新しい薬をデータベースに保存
     * POST /medications
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        dump($request->all()); // ★追加: フォームから送信された全てのデータを確認
        $validatedData = $request->validate([
            'medication_name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'effect' => 'nullable|string',
            'side_effects' => 'nullable|string',
        ]);
        dump($validatedData); // ★追加: バリデーション後のデータを確認

        $medication = Medication::create($validatedData);
        return redirect()->route('medications.index')->with('success', '薬が正常に追加されました！');
    }

    /**
     * 特定の薬の詳細を表示
     * GET /medications/{medication}
     *
     * @param  \App\Models\Medication  $medication
     */
    public function show(Medication $medication)
    {
        return view('medications.show', compact('medication'));
    }

    /**
     * 特定の薬の編集フォームを表示
     * GET /medications/{medication}/edit
     *
     * @param  \App\Models\Medication  $medication
     */
    public function edit(Medication $medication)
    {
        return view('medications.edit', compact('medication'));
    }

    /**
     * 特定の薬をデータベースで更新
     * PUT/PATCH /medications/{medication}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Medication  $medication
     */
    public function update(Request $request, Medication $medication)
    {
        dump($request->all()); // ★追加: フォームから送信された全てのデータを確認
        dump($medication);     // ★追加: 更新対象のMedicationモデルのインスタンスを確認
        $validatedData = $request->validate([
            'medication_name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'effect' => 'nullable|string',
            'side_effects' => 'nullable|string',
        ]);
        dump($validatedData); // ★追加: バリデーション後のデータを確認

        $medication->update($validatedData);

        return redirect()->route('medications.show', $medication->medication_id)->with('success', '薬の情報が正常に更新されました！');
    }

    /**
     * 特定の薬をデータベースから削除
     * DELETE /medications/{medication}
     *
     * @param  \App\Models\Medication  $medication
     */
    public function destroy(Medication $medication)
    {
        dump($medication);
        $medication->delete();

        return redirect()->route('medications.index')->with('success', '薬が正常に削除されました！');
    }
}