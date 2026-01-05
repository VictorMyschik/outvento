@component($typeForm, get_defined_vars())
    <div data-controller="ckeditor5"
         data-ckeditor5-id-value="{{ $id }}"
    >
        <div data-ckeditor5-target="ckeditor"></div>
        <input id="{{ $id }}"
               data-ckeditor5-target="input"
               name="{{ $name }}"
               type="hidden"
               class="form-control"
               value="{{ $value ?? '' }}"
        />
    </div>
    <style>
        .ck-content a {
            color: blue;
        }
        .ck-rounded-corners .ck.ck-balloon-panel, .ck.ck-balloon-panel.ck-rounded-corners {
            z-index: 10055 !important;
        }
        .ck-editor__editable_inline:not(.ck-comment__input *) {
            height: 280px;
            overflow-y: auto;
        }
    </style>
@endcomponent
