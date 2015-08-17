<div class="modal fade" id="sprint-settings-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Sprint settings</h4>
            </div>
            {!! Form::model($sprint, ['route' => ['sprint_settings_path', $sprint->phabricator_id], 'method' => 'PUT']) !!}
            <div class="modal-body">
                <p>
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('ignore_estimates') !!}
                        Ignore story point estimates
                    </label>
                </div>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="save-sprint-settings">Save</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
