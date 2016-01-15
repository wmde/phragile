<?php

class ProjectsController extends Controller {

	public function show(Project $project)
	{
		$currentSprint = $project->currentSprint();

		return $currentSprint ? App::make('SprintsController')->show($currentSprint)
		                      : View::make('project.view', compact('project'));
	}

	public function index()
	{
		return View::make('project.index')->with('projects', Project::all());
	}

	public function store()
	{
		$title = Input::get('title');
		$project = new Project([
			'title' => $title,
			'slug' => Str::slug($title)
		]);
		$validation = $project->validate();

		if ($validation->fails())
		{
			foreach ($validation->messages()->all() as $error)
			{
				Flash::error($error);
			}
		} elseif ($project->save())
		{
			Flash::success('Successfully created the new project');
		} else
		{
			Flash::error('Could not create the project. Please try again');
		}

		return Redirect::back();
	}

	public function updateSettings(Project $project)
	{
		$project->update(Input::only('closed_statuses', 'workboard_mode', 'ignored_columns', 'backlog_column'));

		Flash::success('The project settings have been updated');
		return Redirect::back();
	}
}
