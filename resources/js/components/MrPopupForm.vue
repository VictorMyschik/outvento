<template  style="z-index: 9999;">
  <span>
    <span v-if="showModal">
      <transition name="modal fade">
        <div class="modal-mask">
          <div class="modal-wrapper">
            <div class="modal-dialog mw-100" :class="size" role="document">
              <div class="modal-content">
                <div class="p-b-25 modal-header shadow btn-panel"
                     style="height: 30px; border-radius: 0; border-color: #a34701">
                  <h6 class=" mr-bold">{{ title }}</h6>
                  <button type="button" class="mr-btn-primary fa fa-window-close" @click="showModal = false"
                          data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form action="#" method="post" v-on:submit.prevent="MrSave" id="frm">
                    <input type="hidden" name="_method" value="put">
                    <div class="mr-bold mr-middle" v-if="load_data">Загрузка данных <i
                        class="fa fa-spinner fa-spin"></i></div>
                    <div class="row no-gutters text-danger">
                      <div id="mrError"></div>
                    </div>
                    <v-runtime-template :template="rendered"></v-runtime-template>
                  </form>
                </div>
                <div class="modal-footer justify-content-center" style="height: 55px;">
                  <span v-if="btnInfo">
                     <button type="button" @click="hide()" class="mr-btn-primary">Закрыть</button>
                  </span>
                  <span v-else>
                    <button type="button" v-if="!is_wait" @click="MrSave()" class="mr-btn-primary">Сохранить</button>
                    <span v-else><i class="fa fa-spinner fa-spin"></i></span>
                    <button type="button" @click="hide()" class="mr-btn-danger m-l-15">Отменить</button>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </span>

    <span @click="show()" :class="class_arr" @keyup.esc="hide" v-html="btn_name"></span>
  </span>
</template>

<script>
import VRuntimeTemplate from 'vue3-runtime-template';

export default {
    components: {
        VRuntimeTemplate,
    },
    name: "MrPopupForm",
    props: [
        'route_url',
        'btn_name',
        'class_arr',
        'need_reload', // нужно ли перезагружать страницу
    ],
    data() {
        return {
            showModal: false,
            rendered: null,
            //form_data: [],
            title: '',
            size: 'w-50',
            url: '',
            btnInfo: false,
            load_data: false,
            mrErrors: null,
            is_wait: false,
        }
    },

    created: function () {
        document.addEventListener('keyup', this.escPress);
    },

    methods: {
        escPress(event) {
            if (event.keyCode === 27) {
                this.hide();
            }
        },

        show() {
            this.getForm();
            this.showModal = true;
            this.mrErrors = null;
        },
        hide() {
            this.mrErrors = null;
            this.showModal = false
        },

        getForm: function () {
            this.load_data = true;
            axios.post(this.route_url).then(response => {
                    let data = response.data;
                    this.rendered = data.rendered;

                    this.system = data.system;
                    this.title = this.system['#title'];
                    this.size = this.system['#size'];
                    this.url = this.system['#url'];
                    this.btnInfo = this.system['#btnInfo'];

                    this.load_data = false;
                    this.is_wait = false;
                }
            );
        },

        MrSave: function () {
            document.getElementById('mrError').innerHTML = '';
            this.in_data = {};
            this.is_wait = false;
            let myForm = document.getElementById('frm');
            let formData = new FormData(myForm);
            // need to convert it before using not with XMLHttpRequest
            for (let [key, val] of formData.entries()) {
                Object.assign(this.in_data, {[key]: val});
                let tmpElement = document.getElementById(key);
                if (undefined !== tmpElement && null !== tmpElement) {
                    tmpElement.style.border = "1px solid #ced4da";
                }
            }

            if (this.url !== undefined) {
                axios.put(this.url, this.in_data)
                    .then(response => {
                        this.hide();
                        this.afterSave();
                        this.is_wait = false;
                    })
                    .catch(error => {
                        this.buildErrorHtml(error.response.data.error);
                    });
            }
        },

        afterSave: function () {
            if (this.need_reload == 1) {
                window.location.reload();
            } else {
                this.$emit('response', {
                    result: this.in_data,
                    args: this.form_data,
                    url: this.route_url
                });

                this.hide();
            }
        },

        buildErrorHtml: function (response) {
            let errorMessageHtml = '';
            let msg = JSON.parse(response);
            let el;
            for (let r in msg) {
                errorMessageHtml += '<div>' + msg[r] + '</div>';
                el = document.getElementById(r);
                if (el) {
                    el.style.border = "red solid 1px";
                }
            }
            errorMessageHtml += '</div><hr>';
            document.getElementById('mrError').innerHTML = errorMessageHtml;

        },
    }
}
</script>

<style scoped>

.modal-mask {
    position: fixed;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    webkit-transform: translate3d(0, 0, 0) !important;
    background-color: rgba(0, 0, 0, .5);
    display: table;
    transition: opacity 0.3s ease;
}

.modal-wrapper {
    display: table-cell;
    vertical-align: middle;
}

.modal-body {
    overflow-y: auto;
    max-height: 75vh;
}
</style>
