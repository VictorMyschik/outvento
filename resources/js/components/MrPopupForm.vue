<template>
  <span>
    <span v-if="showModal">
      <transition name="modal fade">
        <div class="modal-mask">
          <div class="modal-wrapper">
            <div class="modal-dialog mw-100" :class="size" role="document">
              <div class="modal-content mr-background-form pt-0 px-1">
                <div class="p-1 modal-header shadow btn-panel"
                     style="height: auto; vertical-align: center; border-color: #a34701">
                    <h4 class="px-3 pt-2">{{ title }}</h4>
                  <button type="button" class="mr-btn-primary fa fa-window-close" @click="showModal = false"
                          data-dismiss="modal" aria-label="Close" style="margin-left: auto;"></button>
                </div>
                <div class="modal-body py-3 px-3">
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
                    <button type="button" v-if="!is_wait" @click="MrSave()" class="mr-btn-primary"> {{ lang['save'] }} </button>
                    <span v-else><i class="fa fa-spinner fa-spin"></i></span>
                    <button type="button" @click="hide()" class="mr-btn-danger m-l-15"> {{ lang['cancel'] }} </button>
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
        'lang',
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
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.4s ease;
}

/* Убираем table-cell и делаем flex — теперь по центру на любом экране */
.modal-wrapper {
    width: 100%;
    padding: 20px;
    display: flex;
    justify-content: center;
}

/* Кнопка закрытия — белый крестик */
.mr-btn-primary.fa-window-close {
    color: white;
    font-size: 1.5rem;
    opacity: 0.9;
    transition: opacity 0.2s;
}

.mr-btn-primary.fa-window-close:hover {
    opacity: 1;
}

/* Кнопки внизу */
.mr-btn-primary,
.mr-btn-danger {
    min-width: 110px;
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 300;
}

@media (max-width: 768px) {
    .modal-wrapper {
        padding: 70px 0 0 0 !important; /* отступ сверху 10px (можно 15px) */
        align-items: flex-end;
    }

    .modal-content.mr-background-form {
        border-radius: 24px 24px 0 0;
        margin-top: 10px; /* гарантированный отступ от верха */
        max-height: calc(100vh - 20px); /* не больше 100% высоты минус отступы */
    }

    .modal-dialog {
        width: 100vw !important;
        max-width: 100vw !important;
        margin: 0 !important;
    }

    .modal-content.mr-background-form {
        border-radius: 24px 24px 0 0 !important;
        width: 100vw !important;
        margin: 0 !important;
    }
}
</style>
