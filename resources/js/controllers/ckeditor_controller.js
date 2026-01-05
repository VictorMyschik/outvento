import {
    AdjacentListsSupport,
    AutoLink,
    Base64UploadAdapter,
    BlockQuote,
    Bold,
    ClassicEditor,
    Clipboard,
    Essentials,
    Font,
    FontBackgroundColor,
    FontColor,
    FontSize,
    Heading,
    HorizontalLine,
    Image,
    ImageCaption,
    ImageInsertUI,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    IndentBlock,
    Italic,
    GeneralHtmlSupport,
    Link,
    LinkImage,
    List,
    Paragraph,
    PasteFromMarkdownExperimental,
    PasteFromOffice,
    PastePlainText,
    SourceEditing,
    Strikethrough,
    Table,
    TableToolbar,
} from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';
import './custom.css';
import coreTranslations from 'ckeditor5/translations/ru.js';
import FullScreen from "@pikulinpw/ckeditor5-fullscreen";

import {Controller} from '@hotwired/stimulus'
import {useMeta} from 'stimulus-use'

export default class extends Controller {

    static targets = [
        'ckeditor',
        'input',
    ]

    plugins = [
        Clipboard, Heading, AdjacentListsSupport,
        PasteFromOffice, FontSize, FontBackgroundColor,
        FontColor, Strikethrough, BlockQuote, List,
        PasteFromMarkdownExperimental, PastePlainText,
        Essentials, Bold, Italic, Font, Paragraph, Link, Table,
        TableToolbar, SourceEditing, AutoLink, HorizontalLine,
        Image, ImageToolbar, ImageCaption, ImageStyle, ImageResize,
        LinkImage, Base64UploadAdapter, ImageUpload, GeneralHtmlSupport,
        ImageInsertUI, FullScreen, Indent, IndentBlock
    ]

    toolbar = [
        'undo', 'redo',
        '|',
        'heading',
        '|',
        'bold', 'italic', 'strikethrough',
        '|',
        'fontsize', 'fontColor', 'fontBackgroundColor',
        '|',
        'link', 'blockQuote',
        '|',
        'bulletedList', 'numberedList', "outdent", "indent",
        '|',
        'insertTable', 'horizontalLine',
        '|',
        'sourceEditing',
        '|',
        'insertImage', 'fullscreen'
    ]

    initialize() {
        useMeta(this)
    }

    async connect() {
        /* фикс бага с модалками (фокус) */
        Array.from(document.querySelectorAll('[data-controller="modal"]'))
            .forEach(o => o.dataset.bsFocus = "false")

        await ClassicEditor
            .create(this.ckeditorTarget, {
                language: 'ru',
                plugins: this.plugins,
                toolbar: this.toolbar,
                table: {contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']},
                translations: [coreTranslations],
                htmlSupport: {
                    allow: [
                        {
                            name: /.*/,
                            attributes: true,
                            classes: true,
                            styles: true
                        }
                    ]
                },
                link: {
                    // Automatically add target="_blank" and rel="noopener noreferrer" to all external links.
                    addTargetToExternalLinks: true,
                    decorators: {
                        toggleDownloadable: {
                            mode: 'manual',
                            label: 'Downloadable',
                            attributes: {
                                download: 'file'
                            }
                        },
                        openInNewTab: {
                            mode: 'manual',
                            label: 'Open in a new tab',
                            defaultValue: true,			// This option will be selected by default.
                            attributes: {
                                target: '_blank',
                                rel: 'noopener noreferrer'
                            }
                        }
                    }
                },
                image: {
                    toolbar: [
                        "toggleImageCaption", "imageTextAlternative", "|",
                        "imageStyle:inline", "imageStyle:block", "imageStyle:wrapText", "|",
                        "resizeImage:100", "resizeImage:200", "resizeImage:original", "resizeImage:custom", "|",
                        "ckboxImageEdit"
                    ],
                    resizeOptions: [{
                        name: "resizeImage:original",
                        value: null,
                        icon: "original"
                    }, {
                        name: "resizeImage:custom",
                        value: "custom",
                        icon: "custom"
                    }, {
                        name: "resizeImage:100",
                        value: "100",
                        icon: "medium"
                    }, {
                        name: "resizeImage:200",
                        value: "200",
                        icon: "large"
                    }],
                    resizeUnit: "px"
                }
            })
            .then((editor) => this.onLoad(editor))
            .catch(e => console.error(e))
    }

    onLoad(editor) {
        editor.model.document.on('change', () => {
            const data = editor.getData()

            if (this.inputTarget.value !== data) {
                this.inputTarget.value = data
            }
        })

        console.log(Array.from(editor.ui.componentFactory.names()));

        editor.setData(this.inputTarget.value)
    }
}
