<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use Illuminate\Http\Request;
use App\Http\Requests\MedicationStoreRequest;
use App\Http\Requests\MedicationUpdateRequest;
use Illuminate\Support\Facades\Log; // Log も引き続き利用可能
use Illuminate\Support\Facades\Auth;

class MedicationController extends Controller
{

    public function index(Request $request)
    {
        $query = Medication::query();

        // 検索クエリがあればフィルタリングを適用
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('medication_name', 'like', '%' . $search . '%')
                ->orWhere('effect', 'like', '%' . $search . '%');
            });
        }
        $medications = $query->paginate(6); // 1ページに6件表示

        return view('medications.index', compact('medications')); // 'categories' も削除
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
    public function store(MedicationStoreRequest $request)
    {
        $validatedData = $request->validated(); // 検証済みのデータを取得

        $medication = Medication::create($validatedData);
        return redirect()->route('medications.index')->with('success', '薬が正常に追加されました！');
    }

    /**
     * 特定の薬の詳細を表示
     * GET /medications/{medication}
     *
     * @param  \App\Models\Medication  $medication
     */
    public function show(Medication $medication,Request $request)
    {
        $from_date = $request->query('from_date');
        $from_post_id = $request->query('from_post_id');
        $from_medication_id = $request->query('from_medication_id');

        return view('medications.show', compact('medication','from_date','from_post_id', 'from_medication_id'));
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

        $validatedData = $request->validate([
            'medication_name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'effect' => 'nullable|string',
            'side_effects' => 'nullable|string',
        ]);

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

        $medication->delete();

        return redirect()->route('medications.index')->with('success', '薬が正常に削除されました！');
    }
}