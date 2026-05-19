<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->companyId();

        $assignments = Assignment::with([
            'asset:id,asset_name,asset_code',
            'employee:id,full_name,email_address,department_name,job_title',
        ])
            ->where('user_id', $companyId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->success($assignments, 'Assignments fetched');
    }

    public function show(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $assignment = $this->findAssignment($companyId, $id, true);

        if (!$assignment) {
            return response()->error('Assignment not found', 404);
        }

        return response()->success($assignment, 'Assignment fetched');
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->companyId();

        $request->validate([
            'asset_id' => [
                'required',
                'integer',
                Rule::exists('assets', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('user_id', $companyId);
                }),
            ],
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('user_id', $companyId);
                }),
            ],
            'status' => 'required|string|max:50',
            'assignment_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after_or_equal:assignment_date',
            'expected_return' => 'nullable|date|after_or_equal:assignment_date',
            'notes' => 'nullable|string|max:1000',
            'assignment_notes' => 'nullable|string|max:1000',
        ]);

        $asset = Asset::where('user_id', $companyId)->find($request->asset_id);
        if (!$asset) {
            return response()->error('Asset not found', 404);
        }

        $employee = Employee::where('user_id', $companyId)->find($request->employee_id);
        if (!$employee) {
            return response()->error('Employee not found', 404);
        }

        $expectedReturnDate = $request->input('expected_return_date', $request->input('expected_return'));
        $notes = $request->input('notes', $request->input('assignment_notes'));

        $assignment = Assignment::create([
            'user_id' => $companyId,
            'asset_id' => $asset->id,
            'employee_id' => $employee->id,
            'status' => $request->status,
            'assignment_date' => $request->assignment_date,
            'expected_return_date' => $expectedReturnDate,
            'notes' => $notes,
        ]);

        if (!$assignment->assignment_code) {
            $assignment->assignment_code = sprintf('ASSIGN-%03d', $assignment->id);
            $assignment->save();
        }

        $assignment->refresh();

        return response()->created($assignment, 'Assignment created');
    }

    public function update(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $assignment = $this->findAssignment($companyId, $id);
        if (!$assignment) {
            return response()->error('Assignment not found', 404);
        }

        $request->validate([
            'asset_id' => [
                'required',
                'integer',
                Rule::exists('assets', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('user_id', $companyId);
                }),
            ],
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('user_id', $companyId);
                }),
            ],
            'status' => 'required|string|max:50',
            'assignment_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after_or_equal:assignment_date',
            'expected_return' => 'nullable|date|after_or_equal:assignment_date',
            'notes' => 'nullable|string|max:1000',
            'assignment_notes' => 'nullable|string|max:1000',
        ]);

        $asset = Asset::where('user_id', $companyId)->find($request->asset_id);
        if (!$asset) {
            return response()->error('Asset not found', 404);
        }

        $employee = Employee::where('user_id', $companyId)->find($request->employee_id);
        if (!$employee) {
            return response()->error('Employee not found', 404);
        }

        $expectedReturnDate = $request->input('expected_return_date', $request->input('expected_return'));
        $notes = $request->input('notes', $request->input('assignment_notes'));

        $assignment->update([
            'asset_id' => $asset->id,
            'employee_id' => $employee->id,
            'status' => $request->status,
            'assignment_date' => $request->assignment_date,
            'expected_return_date' => $expectedReturnDate,
            'notes' => $notes,
        ]);

        if (!$assignment->assignment_code) {
            $assignment->assignment_code = sprintf('ASSIGN-%03d', $assignment->id);
            $assignment->save();
        }

        return response()->success($assignment, 'Assignment updated');
    }

    public function destroy(Request $request, $id)
    {
        $companyId = $request->user()->companyId();

        $assignment = $this->findAssignment($companyId, $id);
        if (!$assignment) {
            return response()->error('Assignment not found', 404);
        }

        $assignment->delete();

        return ApiResponse::noContent('Assignment deleted');
    }

    private function findAssignment(int $companyId, string $id, bool $withRelations = false): ?Assignment
    {
        $query = Assignment::where('user_id', $companyId);

        if ($withRelations) {
            $query->with([
                'asset:id,asset_name,asset_code',
                'employee:id,full_name,email_address,department_name,job_title',
            ]);
        }

        if (ctype_digit($id)) {
            return $query->find((int) $id);
        }

        return $query->where('assignment_code', $id)->first();
    }
}
