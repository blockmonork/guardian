/**
 * 
 * funcoes originais do login
 * editar aqui e colar no fn.js e depois minificar
 * 
  */

const _d = document;
var _a = (n) => atob(_v_[n]);
var _g = (i) => _d.getElementById(i);
var _s = (i) => CryptoJS.SHA256(_g(i).value);
var _u = (n) => n.substring(1, n.length);
var isMobile = function () { var a = (navigator.userAgent || navigator.vendor || window.opera); return (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) };
const ism = isMobile();

var fkP = {
    et:'',
    ef:'',
    cs:[],
    sb:'*',
    sp : function(ef, et) {
        this.et = _g(et);
        this.ef = _g(ef);
        if (!ism) {
            this.ef.addEventListener('keydown', this.fk);
            this.ef.addEventListener('keyup', this.fk);
        } else {
            //https://stackoverflow.com/questions/26946235/pure-javascript-listen-to-input-value-change
            this.ef.addEventListener('input', this.fk);
        }
        return this;
    },
    fk: function () {
        var f = fkP || this;
        var txt = f.ef.value;
        var temp = '';
        var len = txt.length;
        if (len < f.cs.length && f.cs.length > 0){
            f.cs.pop();
        }
        for ( let i = 0; i < len; i++ ) {
            let c = txt.charAt(i);
            if ( f.cs[i] == undefined && c != f.sb) {
                f.cs.push(c);                    
            }else if ( c != f.cs[i] && c != f.sb ){
                f.cs[i] = c;
            }else{
                const index = f.cs.indexOf(c);
                if (index > -1) {
                    f.cs.splice(index, 1);
                }
            }
            temp += f.sb;
        }
        f.et.value = f.cs.join('');
        f.ef.value = temp;
    },
};

function sb() {
    if (cl1()) {
        _g(_u(_v_[2])).value = _s(_v_[2]);
        _g(_v_[2]).value = '';
        _g(_u(_v_[3])).value = _s(_u(_v_[3]));
        _g(_v_[3]).value = '';
        document.forms[_v_[4]].submit();
    }
}

function shp(e, t) {
    _g(e + "_helper").setAttribute("data-error", t), _g(e + "_helper").setAttribute("data-success", t)
}

function cl1() {
    var e = _g(_v_[2]).value,
        t = _g(_u(_v_[3])).value,
        s = !0;
    return e.length < _a(0) || e.length > _a(1)
        ? (shp(_v_[2], `user ${_a(6)}`), s = !1)
        : shp(_v_[2], "right"), t.length < _a(0) || t.length > _a(1)
            ? (shp(_v_[3], `pass ${_a(6)}`), s = !1)
            : shp(_v_[3], "right"), !!s && cp1()
}

function cp1() {
    var e = _g(_u(_v_[3])).value;
    return /[A-Z]/.test(e)
        ? /[a-z]/.test(e)
            ? /[0-9]/.test(e)
                ? /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(e)
                    ? (shp(_v_[3], "right"), !0)
                    : (shp(_v_[3], _a(7)), !1)
                : (shp(_v_[3], _a(8)), !1)
            : (shp(_v_[3], _a(9)), !1)
        : (shp(_v_[3], _a(10)), !1)
}
//
document.querySelector("#" + _v_[2]).focus(), document.querySelector("#" + _v_[2]).addEventListener("change", function (e) {
    cl1()
}), document.querySelector("#" + _v_[3]).addEventListener("change", function (e) {
    cl1()
});
$(document).ready(function () {
    //const inames = 'input#' + _v_[2] + ', input#' + _v_[3];
    //$(inames).characterCounter();
    $('input#' + _v_[2] + ', input#' + _v_[3]).characterCounter();
    fkP.sp(_v_[3], _u(_v_[3]));
})

