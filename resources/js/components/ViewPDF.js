var ViewPDF = {

    name: 'ViewPDF',
    props: {
        url: String,
        init_scale: {
            type: Number,
            default: 1.5
        },
        id_file: {
            type: Number,
            default: null
        },
        print: {
            type: Boolean,
            default: true
        },
        download: {
            type: Boolean,
            default: true
        },
        save: {
            type: Boolean,
            default: true
        },
        width: Number,
        paginate: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            list_pages: [],
            pages: 0,
            page: 1,
            zoom: null,
            pageIsRendering: false,
            pageNumIsPending: null,
            pdfDoc: null,
            canvas: "#pdf-render",
            ctx: "2d",
            pdfjsLib: window['pdfjs-dist/build/pdf'],
            pdfviewer: window['pdfjs-dist/web/pdf_viewer'],
            current: null,
            viewport: null,
            load_state: null,
            annotattionsStyles: {
                'border': '1px solid #000',
                'background-color': 'rgba(255, 255, 0, 0.5)',
                "min-height": "0px",
                "padding": "0px"
            },
            menu_phone: [
                {
                    icon: 'pi pi-download',
                    label: 'Download',
                    command: () => {
                        this.downloadAction();
                    }
                }, {
                    icon: 'pi pi-print',
                    label: 'Print',
                    command: () => {
                        this.printAction();
                    }
                }, {
                    icon: 'pi pi-save',
                    label: 'Save',
                    command: () => {
                        this.saveAction();
                    }
                }
            ]

        }
    },
    components: {
        "p-button": primevue.button,
        "p-menu": primevue.menu
    },
    methods: {
        toggle_menu(evt) {
            this.$refs.menu_phone.toggle(evt);
        },

        async renderPDF() {

            let self = this;
            let PDFLinkService = self.pdfviewer.PDFLinkService;
            let linkService = new PDFLinkService();
            try {
                let pdfDoc = await self.pdfjsLib.getDocument(self.url).promise.then(async function (pdfDoc) {
                    self.pages = pdfDoc.numPages;
                    self.pdfDoc = pdfDoc;

                    function changePage() {
                        self.load_state = 'loading';
                        try {


                            pdfDoc.getPage(self.page).then(function (page) {
                                self.current = page;
                                self.pageIsRendering = true;
                                let scale = self.zoom;
                                let viewport;

                                viewport = page.getViewport({scale: scale});
                                self.viewport = viewport;

                                if (self.width) {
                                    scale = self.width / viewport.width;
                                }

                                let canvas = document.querySelector(self.canvas);
                                let ctx = canvas.getContext(self.ctx);
                                canvas.height = viewport.height;
                                canvas.width = viewport.width;
                                let container = document.querySelector('.container-pdf');
                                container.style.height = viewport.height + 'px';
                                container.style.width = viewport.width + 'px';
                                let renderCtx = {
                                    canvasContext: ctx,
                                    viewport
                                };
                                let renderTask = page.render(renderCtx);
                                renderTask.promise.then(function () {
                                    self.pageIsRendering = false;
                                    if (self.pageNumIsPending !== null) {
                                        changePage(self.pageNumIsPending);
                                        self.pageNumIsPending = null;
                                    }
                                    self.smoothScroll();
                                });
                                // habilita la selección de texto en la página
                                page.getTextContent().then(function (textContent) {
                                    const textLayer = document.querySelector('.textLayer');
                                    const divs = [];

                                    self.pdfjsLib.renderTextLayer({textContentSource: textContent, container: textLayer, viewport, textDivs: divs});
                                    let html = document.querySelector('html');
                                    html.style.setProperty('--scale-factor', self.zoom);
                                });

                                page.getAnnotations().then(function (annotationss) { // verificar si existe una anotacion de campo editable como checkbox radiobuttons text textarea etc..

                                    const annotationLayer = document.querySelector('.annotation-layer');
                                    let downloadManager = new self.pdfviewer.DownloadManager({disableCreateObjectURL: false});
                                    let layer = new self.pdfviewer.AnnotationLayerBuilder({pageDiv: annotationLayer, linkService: linkService, pdfPage: page, downloadManager: downloadManager});
                                    let annotations = document.querySelectorAll('.annotation-layer > div');
                                    annotations.forEach(function (annotation) {
                                        if (annotation.dataset.pageNumber != self.page) {
                                            annotation.style.display = 'none';
                                        }

                                    });
                                    layer.render(viewport);
                                    self.load_state = 'complete';


                                    document.dispatchEvent(new CustomEvent('saveAnnotations', {
                                        detail: {
                                            annotations: annotationss,
                                            page
                                        }
                                    }));


                                }).catch(function (err) {
                                    console.log(err);
                                });

                            }).catch(function (err) {
                                console.log(err);
                            });
                        } catch (err) {
                            console.log(err);
                        }
                    }

                    changePage();

                    document.addEventListener('update_pdf', function (e) {
                        self.page = e.detail.page;
                        changePage();
                    });

                });
            } catch (err) {
                var div = document.createElement('div');
                div.className = 'error';
                div.appendChild(document.createTextNode(err.message));
                let canvas = document.querySelector(self.canvas);
                document.querySelector('.view-pdf').insertBefore(div, canvas);
                document.querySelector('.top-bar').style.display = 'none';
                return null;
            }
        },

        manager_annotations() {
            let self = this;
            document.addEventListener('update_annotation', function (e) {
                let annotation = e.detail.annotations;
                let page = e.detail.page;
                annotation.forEach(function (annotation) {
                    if (annotation.fieldType === 'Tx') {
                        console.log(annotation);
                    }
                });
            });
        },
        prev() {
            if (this.page <= 1) {
                return;
            }
            this.page --;
            if (this.pageIsRendering) {
                this.pageNumIsPending = this.page;
            } else {
                document.dispatchEvent(new CustomEvent('update_pdf', {
                    detail: {
                        page: this.page
                    }
                }));
            }
        },
        arrowKeys(e) {
            if (e.keyCode == 37) {
                this.prev();
            } else if (e.keyCode == 39) {
                this.next();
            }
        }, // Show Next Page
        next() {
            if (this.page >= this.pages) {
                return;
            }
            this.page ++;
            if (this.pageIsRendering) {
                this.pageNumIsPending = this.page;
            } else {
                document.dispatchEvent(new CustomEvent('update_pdf', {
                    detail: {
                        page: this.page
                    }
                }));
            }
        },
        smoothScroll() {
            let pdf = document.querySelector('.header-pdf');
            pdf.scrollIntoView({behavior: 'smooth'});
        },
        printAction() {
            let printIframe = document.getElementById('printIframe');
            if (printIframe) {
                printIframe.parentNode.removeChild(printIframe);
            }
            let iframe = document.createElement('iframe');
            iframe.id = 'printIframe';
            iframe.src = this.url;
            document.body.appendChild(iframe);
            iframe.style.display = 'none';
            iframe.contentWindow.print();
        },
        async saveDocument() {
            let id_file = this.id_file;
            let annots = await this.pdfjsLib.getDocument(this.url).promise.then(function (pdf) {
                let promises = {};

                for (let i = 1; i <= pdf.numPages; i++) {
                    promises[i] = pdf.getPage(i).then(function (page) {
                        return page.getAnnotations();
                    });
                }
                for (let i = 1; i <= pdf.numPages; i++) {
                    promises[i] = Promise.all([promises[i], pdf.getPage(i)]);
                }

                return Promise.all(Object.keys(promises).map(function (id) {
                    return promises[id];
                }));


            });
            let new_annots = {};
            for (let i = 0; i <= annots.length; i++) {
                if (annots[i] ?. [0].length > 0) {
                    new_annots[i] = annots[i][0];
                }
            }
            console.log(new_annots);

            let data = {
                id_file,
                annotations: new_annots
            };
            console.log(JSON.stringify(data));
            fetch('http://dev.test/wp-json/ui/v1/set_pdf_annotations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                console.log(data);
            }).catch(function (err) {
                console.log(err);
            });

        }


    },
    watch: {
        zoom: function (val, old) { // min 0.5 max 2
            if (val >= 0.5 && val <= 2) {
                document.dispatchEvent(new CustomEvent('update_pdf', {
                    detail: {
                        page: this.page
                    }
                }));
            } else {
                this.zoom = old;
            }
        },
        url: function (val, old) {
            console.log("val", val);
            this.renderPDF();
        }

    },
    mounted() {
        this.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.2.146/pdf.worker.min.js';
        this.zoom = this.zoom ? this.zoom : this.init_scale;
        // this.renderPDF();

        document.addEventListener('saveAnnotations', (e) => {
            this.list_pages.push({page: e.detail.page, annotations: e.detail.annotations});
        });

        document.addEventListener('keydown', this.arrowKeys);
        this.manager_annotations();
        let style = document.createElement('style');
        style.innerHTML = `

            .header-pdf {
                margin-top: -1px;
                box-shadow: -12px 17px 20px -18px #ffffff26;
            }
            .container-pdf{
                background: #eaeaea !important;
                box-shadow: 0px 0px 10px 0px #0000002e;
                margin-top: 30px;
            }
            .global-pdf-container{
                padding-bottom: 20px;
                padding-top: 20px;
            }
            .page-number-pdf{
                font-weight: 600;
                font-family: 'League Spartan';
                color: #797979;
            }
            .menu-phone-pdf{
                display: none;
            }
            @media (max-width: 782px) {
                .menu-phone-pdf{
                    display: block !important;
                }
                .view-pdf .p-button.p-button-icon-only
                {
                
                }
                .auto-fold #wpcontent{
                    padding-left: 0px !important;
                }
            }
        `;
        document.head.appendChild(style);

    },

    template: `
    <div v-show="load_state=='complete'">
        <div class="view-pdf d-flex justify-content-center flex-column align-items-center ">
            <header class="bg-light header-pdf w-100 p-2">
                <div class="top-bar d-flex justify-content-center align-items-center position-relative">
                    <div class="d-flex">
                        <p-button icon="pi pi-arrow-left" v-if="pages > 1" @click="prev" class=" p-button-rounded p-button-sm  me-1 p-button-text"></p-button>
                        <p-button icon="pi pi-arrow-right" v-if="pages > 1" @click="next" class=" p-button-rounded p-button-sm  me-1 p-button-text"></p-button>
                    </div>
                    <span class="mx-2 page-number-pdf">{{page}} / {{pages}}</span>
                    <div class="d-flex">
                        <p-button icon="pi pi-plus" @click="zoom += 0.2" class=" p-button-rounded p-button-sm  p-button-text"></p-button>
                        <p-button icon="pi pi-minus" @click="zoom -= 0.2" class=" p-button-rounded p-button-sm ms-1 p-button-text"></p-button>
                    </div>
                        <p-button icon="pi pi-print" v-if="print" @click="printAction"class="d-md-block d-none p-button-rounded p-button-sm  p-button-success ms-1 p-button-text position-absolute" style="right:10px;"></p-button>
                        <p-button icon="pi pi-download" v-if="download" @click="downloadAction" class=" d-md-block d-none p-button-rounded p-button-sm  p-button-success ms-1 p-button-text position-absolute" style="right:40px;"></p-button>
                        <p-button icon="pi pi-save"  v-if="save"  @click="saveDocument" class=" p-button-rounded d-md-block d-none p-button-sm  p-button-success ms-1 p-button-text position-absolute" style="right:70px;"></p-button>
                        <p-menu :model="menu_phone" :popup="true" ref="menu_phone" v-if="menu_phone.length > 0" class="p-menu-sm "></p-menu>
                        <p-button @click="toggle_menu" v-if="menu_phone.length > 0" icon="pi pi-ellipsis-v" class=" p-button-rounded p-button-sm menu-phone-pdf p-button-success ms-1 p-button-text position-absolute" style="right:0px;"></p-button>

                    </div>
                </div>
            </header>
            <div class="global-pdf-container d-flex justify-content-md-center justify-content-start align-items-center scrollbar-x">
                <div class="container-pdf page position-relative" >
                    <div class="canvasWrapper">
                        <canvas id="pdf-render"></canvas>
                    </div>
                    <div class="textLayer"></div>
                    <div class="annotation-layer"></div>
                </div>
            </div>
        </div>
    </div>
    `
}
