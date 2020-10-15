<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectConfig;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportController extends Controller
{
    public function allocations()
    {
        $filename = 'project_allocations_'.now()->format('d_m_Y').'.xlsx';
        $writer = SimpleExcelWriter::create(public_path($filename));
        $projects = Project::active()->with('owner', 'students', 'acceptedStudents', 'discipline')->orderBy('title')->get();
        $projects->each(function ($project) use ($writer) {
            $writer->addRow([
                'Project Title' => $project->is_active ? $project->title : "[Inactive] {$project->title}",
                'Owner' => $project->owner->fullName(),
                'Sup. Name' => $project->supervisor_name,
                'Sup. Email' => $project->supervisor_email,
                'University' => $project->institution,
                'Disciplines' => $project->getDisciplineTitles(),
                '1st round choices' => $project->roundStudentCount(1),
                'Allocated?' => $project->numberAccepted() ? 'Y' : 'N',
                'Project Description' => $project->description,
            ]);
        });

        return response()->download(public_path($filename));
    }

    public function allStudents()
    {
        $filename = 'all_project_students_'.now()->format('d_m_Y').'.xlsx';
        $writer = SimpleExcelWriter::create(public_path($filename));
        $students = User::students()->with('courses', 'projects')->orderBy('surname')->get();
        // @TODO remove this _if_ we are sure we can avoid having blank entries for the number of projects
        // $singleDegreeReq = ProjectConfig::getOption('single_uog_required_choices', config('projects.single_uog_required_choices'))
        //                     + ProjectConfig::getOption('single_uestc_required_choices', config('projects.single_uestc_required_choices'));
        // $dualDegreeReq = ProjectConfig::getOption('required_choices', config('projects.uog_required_choices'))
        //                     + ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices'));
        // $required = $singleDegreeReq >= $dualDegreeReq ? $singleDegreeReq : $dualDegreeReq;

        $this->buildStudentRows($students, $writer);

        return response()->download(public_path($filename));
    }

    public function singleDegreeStudents()
    {
        $filename = 'single_degree_project_students_'.now()->format('d_m_Y').'.xlsx';
        $writer = SimpleExcelWriter::create(public_path($filename));
        $students = User::students()->singleDegree()->with('courses', 'projects')->orderBy('surname')->get();

        $this->buildStudentRows($students, $writer);

        return response()->download(public_path($filename));
    }

    public function dualDegreeStudents()
    {
        $filename = 'dual_degree_project_students_'.now()->format('d_m_Y').'.xlsx';
        $writer = SimpleExcelWriter::create(public_path($filename));
        $students = User::students()->dualDegree()->with('courses', 'projects')->orderBy('surname')->get();

        $this->buildStudentRows($students, $writer);

        return response()->download(public_path($filename));
    }

    public function staff()
    {
        $filename = 'project_staff_list_'.now()->format('d_m_Y').'.xlsx';
        $writer = SimpleExcelWriter::create(public_path($filename));
        $users = User::staff()->with('projects')->orderBy('surname')->get();
        $users->each(function ($user) use ($writer) {
            $writer->addRow([
                'Name' => $user->fullName(),
                'University' => $user->institution,
                'Projects' => $user->projects->count(),
                'Active Projects' => $user->activeProjects->count(),
                'Inactive Projects' => $user->inactiveProjects->count(),
                'Applied' => $user->totalStudents(),
                'Accepted' => $user->totalAcceptedStudents(),
            ]);
        });

        return response()->download(public_path($filename));
    }

    protected function buildStudentRows(Collection $students, SimpleExcelWriter $writer): void
    {
        $students->each(function ($student) use ($writer) {
            $data = [
                'GUID' => $student->username,
                'Name' => $student->fullName(),
            ];
            $student->projects()->UESTC()->orderBy('preference')->get()->each(function ($project) use ($data) {
                $data["Choice {$project->preference}"] = $project->institution.' '.$project->title;
            });
            $student->projects()->UoG()->orderBy('preference')->get()->each(function ($project) use ($data) {
                $data["Choice {$project->preference}"] = $project->institution.' '.$project->title;
            });
            $writer->addRow($data);
        });
    }
}
