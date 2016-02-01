<button class="btn btn-default btn-lg create-project" data-toggle="modal" data-target="#create-project-form">
    Create a New Project
</button>

<div class="modal fade" id="create-project-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Create a new project</h4>
            </div>
            {!! Form::open(['method' => 'POST', 'route' => 'create_project_path']) !!}
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('title', 'Title') !!}
                        {!! Form::text('title', '', ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
