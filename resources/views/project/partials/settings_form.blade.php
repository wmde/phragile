<div class="modal fade" id="project-settings-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Project settings</h4>
            </div>
            {!! Form::model($sprint->project, ['route' => ['project_settings_path', $sprint->project->slug], 'method' => 'PUT']) !!}
            <div class="modal-body">
                <p>
                    Edit your project settings here. Please note that if you choose to work with Phabricator's workboards and check "Use workboards instead of statuses" you should have at least one column symbolizing a "closed" state for tasks and copy its name to the field below.
                </p>
                <p>
                    <div class="form-group">
                        <label>
                            {!! Form::checkbox('workboard_mode') !!}
                            Use workboards instead of statuses
                        </label>
                    </div>
                    <div class="form-group">
                        {!! Form::label('closed_statuses', 'Closed status columns') !!}
                        {!! Form::text('closed_statuses') !!}
                    </div>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
