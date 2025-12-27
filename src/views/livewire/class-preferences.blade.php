<div classes="{!! $classes !!}" @if($style) style="{!! $style !!}" @endif>
    <h3 class="border-bottom">{{ __('google-integrator::google.services.classroom.google.assignments') }}</h3>
    @foreach($schoolClass->sessions as $session)
        @continue(!$session->classManager instanceof \halestar\FabLmsGoogleIntegrator\Connections\GoogleClassroomConnection)
        <h4>{{ $session->term->label }}</h4>
        <div class="input-group my-3">
            <span class="input-group-text">{{ __('google-integrator::google.services.classroom.google') }}</span>
            <select
                name="class_management_id"
                id="class_management_id"
                class="form-select"
                wire:model="classroomLink.sessions.{{ $session->id }}"
                wire:change="savePreferences()"
            >
                <option value="">{{ __('google-integrator::google.services.classroom.google.select') }}</option>
                @foreach($gCourses as $gCourseId => $gCourse)
                    <option value="{{ $gCourseId }}">{{ $gCourse }}</option>
                @endforeach
            </select>
            <button
                class="btn btn-warning"
                wire:click="createClassroom({{ $session->id }})"
            >{{ __('common.create') }}</button>
        </div>
    @endforeach
    <h3 class="border-bottom">{{ __('google-integrator::google.services.classroom.syncing.options') }}</h3>
    <div class="form-check form-switch">
        <input
            class="form-check-input"
            type="checkbox"
            wire:model="classroomLink.sync_basic"
            wire:click="savePreferences()"
            id="sync_basic"
            switch
        />
        <label class="form-check-label" for="sync_basic">
            {{ __('google-integrator::google.services.classroom.syncing.course') }}
        </label>
    </div>
    <div class="form-check form-switch">
        <input
                class="form-check-input"
                type="checkbox"
                wire:model="classroomLink.sync_students"
                wire:click="savePreferences()"
                id="sync_students"
                switch
        />
        <label class="form-check-label" for="sync_students">
            {{ __('google-integrator::google.services.classroom.syncing.students') }}
        </label>
    </div>
    <div class="form-check form-switch">
        <input
                class="form-check-input"
                type="checkbox"
                wire:model="classroomLink.sync_assignments"
                wire:click="savePreferences()"
                id="sync_assignments"
                switch
        />
        <label class="form-check-label" for="sync_assignments">
            {{ __('google-integrator::google.services.classroom.syncing.lds') }}
        </label>
    </div>
    <div class="row">
        <button
            type="button"
            class="btn btn-primary col"
            wire:click="syncCourseInformation"
        >Sync Class Info</button>
        <button
                type="button"
                class="btn btn-primary col"
        >Sync Student Info</button>
        <button
                type="button"
                class="btn btn-primary col"
        >Sync Assignment Info</button>
    </div>
</div>
