<div class="dropdown">
    <button class="btn btn-lg btn-primary" data-toggle="dropdown">
        Select a Project
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" id="projects">
        @foreach($projects as $project)
            <li>
                {!! link_to_route(
                    'project_path',
                    $project->title,
                    ['project' => $project->slug]
                ) !!}
            </li>
        @endforeach
    </ul>
</div>
