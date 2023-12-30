{{-- 
    ESTE BLADE ESTA COMPARTIDO EN LAS SIGUIENTES RUTAS
    1. views\pages\ajax\user.blade.php
    2. views\pages\ajax\politics\default_politics.blade.php
    3. views\pages\ajax\politics\list_massive_politics.blade.php
 --}}


<div id="perfil" class="tab-pane">
    <div class="row">

        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body" >

                        <div class="row">
                        @foreach($roles as $role)
                            <div class="panel-body col-md-12 col-xs-12 panel-role" id="{{'panel_'.$role->id}}">
                            <h3 class="box-title">{{ $role->title }}</h3>
                            <hr style="margin-bottom: 10px; margin-top: 10px">
                            <div class="row">
                                @foreach($role->policies as $rolePolicie)
                                <div class="col-md-4 col-xs-12 poliClass" id="{{'item_'.$rolePolicie->id }}">
                                    <input type="hidden" name="{{ 'role_id_'.$rolePolicie->roles_id }}" value="{{ $rolePolicie->roles_id }}">
                                    <input type="hidden" name="{{ 'policy_id_'.$rolePolicie->id }}" value="{{ $rolePolicie->id  }}">
                                    @if ($rolePolicie->type == 'CH')
                                    <div class="form-check" style="margin-bottom: 1rem;">
                                        <label style="padding-left: 0px" class="form-check-label bt-switch"
                                        @if (isset($rolePolicie->description) && !empty($rolePolicie->description))
                                            data-toggle="tooltip" data-animation="false" title="{{ $rolePolicie->description }}"
                                        @endif
                                        >
                                        <input type="checkbox" id="{{ 'value_'.$rolePolicie->roles_id.$rolePolicie->id }}" name="{{ 'value_'.$rolePolicie->roles_id.$rolePolicie->id }}" class="form-check-input" style="margin-right: 5px" value="0"><span style="padding-left: 20px">{{ $rolePolicie->name }}</span>
                                        </label>
                                    </div>
                                    @else
                                    <div class="form-group" style="margin-bottom: 0">
                                        <label class="control-label " style="padding-left: 0px">{{ $rolePolicie->name }}</label>
                                        <input type="text" class="" value="{{$rolePolicie->value}}" style="height: 24px; width:100%; max-width: 100px;" id="{{ 'value_'.$rolePolicie->roles_id.$rolePolicie->id }}" name="{{ 'value_'.$rolePolicie->roles_id.$rolePolicie->id }}" class="form-control" {{-- placeholder="{{ $rolePolicie->name }}" --}}
                                            @if (isset($rolePolicie->description) && !empty($rolePolicie->description))
                                            data-toggle="tooltip" data-animation="false" title="{{ $rolePolicie->description }}"
                                            @endif
                                        >
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            </div>
                        @endforeach
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!---->
</div>