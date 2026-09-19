<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Facility;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = Report::query()
            ->with([
                'facility',
                'user',
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category')->toString());
        }

        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->integer('facility_id'));
        }

        $reports = $query
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        $facilities = Facility::query()
            ->orderBy('name')
            ->get();

        return view('officer.reports.index', compact(
            'reports',
            'facilities'
        ));
    }

    public function show(Report $report): View
    {
        $report->load([
            'facility',
            'user',
            'photos',
        ]);

        return view('officer.reports.show', compact('report'));
    }

    public function update(
        UpdateReportRequest $request,
        Report $report
    ): RedirectResponse {
        $report->update($request->validated());

        return redirect()
            ->route('officer.reports.index')
            ->with('success', 'Status laporan berhasil diperbarui.');
    }
}
