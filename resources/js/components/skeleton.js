this.primevue=this.primevue||{},this.primevue.skeleton=function(e){"use strict";var t={name:"Skeleton",props:{shape:{type:String,default:"rectangle"},size:{type:String,default:null},width:{type:String,default:"100%"},height:{type:String,default:"1rem"},borderRadius:{type:String,default:null},animation:{type:String,default:"wave"}},computed:{containerClass(){return["p-skeleton p-component",{"p-skeleton-circle":"circle"===this.shape,"p-skeleton-none":"none"===this.animation}]},containerStyle(){return this.size?{width:this.size,height:this.size,borderRadius:this.borderRadius}:{width:this.width,height:this.height,borderRadius:this.borderRadius}}}};return function(e,t){void 0===t&&(t={});var n=t.insertAt;if(e&&"undefined"!=typeof document){var i=document.head||document.getElementsByTagName("head")[0],r=document.createElement("style");r.type="text/css","top"===n&&i.firstChild?i.insertBefore(r,i.firstChild):i.appendChild(r),r.styleSheet?r.styleSheet.cssText=e:r.appendChild(document.createTextNode(e))}}("\n.p-skeleton {\n    position: relative;\n    overflow: hidden;\n}\n.p-skeleton::after {\n    content: '';\n    animation: p-skeleton-animation 1.2s infinite;\n    height: 100%;\n    left: 0;\n    position: absolute;\n    right: 0;\n    top: 0;\n    transform: translateX(-100%);\n    z-index: 1;\n}\n.p-skeleton.p-skeleton-circle {\n    border-radius: 50%;\n}\n.p-skeleton-none::after {\n    animation: none;\n}\n@keyframes p-skeleton-animation {\nfrom {\n        transform: translateX(-100%);\n}\nto {\n        transform: translateX(100%);\n}\n}\n"),t.render=function(t,n,i,r,s,o){return e.openBlock(),e.createElementBlock("div",{style:e.normalizeStyle(o.containerStyle),class:e.normalizeClass(o.containerClass),"aria-hidden":"true"},null,6)},t}(Vue);