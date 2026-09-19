<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Facility;
use App\Models\Report;
use App\Models\ReportPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function create(Request $request): View
    {
        $facilities = Facility::query()
            ->orderBy('name')
            ->get();

        $selectedFacilityId = $request->integer('facility') ?: null;

        return view('reports.create', compact(
            'facilities',
            'selectedFacilityId'
        ));
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $report = new Report([
            'facility_id' => $validated['facility_id'],
            'category' => $validated['category'],
            'description' => $validated['description'],
        ]);

        $report->user_id = $request->user()->id;
        $report->status = 'baru';
        $report->save();

        $uploadPath = public_path('uploads/reports');

        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        foreach ($request->file('photos') as $photo) {
            $fileName = Str::random(40).'.'.$photo->extension();

            $photo->move($uploadPath, $fileName);

            $reportPhoto = new ReportPhoto();
            $reportPhoto->file_name = $fileName;

            $report->photos()->save($reportPhoto);
        }

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Laporan berhasil dikirim.');
    }
}
