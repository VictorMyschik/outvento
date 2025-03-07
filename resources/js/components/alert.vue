<template>
    <div v-if="showModal">
      <transition name="modal fade">
        <div class="modal-mask">
          <div class="modal-wrapper">
            <div class="modal-dialog mw-100 w-50" role="document">
              <div class="modal-content mr-background-form pt-0 px-1">
                <div class="modal-body py-3 px-3">
                  {{ message }}
                </div>
                <div class="modal-footer justify-content-center" style="height: 55px;">
                     <button type="button" @click="hide()" class="mr-btn-primary">Закрыть</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
</template>

<script>
export default {
    name: "alert_modal",
    props: [
        'message',
    ],
    data() {
        return {
            showModal: false,
        }
    },

    created: function () {
        this.show();
        document.addEventListener('keyup', this.escPress);
    },

    methods: {
        escPress(event) {
            if (event.keyCode === 27) {
                this.hide();
            }
        },

        show() {
            this.showModal = true;
        },
        hide() {
            this.showModal = false
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
