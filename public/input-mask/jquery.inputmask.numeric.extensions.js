!function(d){d.extend(d.inputmask.defaults.aliases,{decimal:{mask:"~",placeholder:"",repeat:"*",greedy:!1,numericInput:!1,isNumeric:!0,digits:"*",groupSeparator:"",radixPoint:".",groupSize:3,autoGroup:!1,allowPlus:!0,allowMinus:!0,integerDigits:"*",defaultValue:"",prefix:"",suffix:"",getMaskLength:function(e,r,a,t,i){var n=e.length;r||("*"==a?n=t.length+1:1<a&&(n+=e.length*(a-1)));var o=d.inputmask.escapeRegex.call(this,i.groupSeparator),l=d.inputmask.escapeRegex.call(this,i.radixPoint),s=t.join(""),p=s.replace(new RegExp(o,"g"),"").replace(new RegExp(l),"");return n+(s.length-p.length)},postFormat:function(e,r,a,t){if(""==t.groupSeparator)return r;var i=e.slice();d.inArray(t.radixPoint,e);a||i.splice(r,0,"?");var n=i.join("");if(t.autoGroup||a&&-1!=n.indexOf(t.groupSeparator)){var o=d.inputmask.escapeRegex.call(this,t.groupSeparator),l=(n=n.replace(new RegExp(o,"g"),"")).split(t.radixPoint);n=l[0];for(var s=new RegExp("([-+]?[\\d?]+)([\\d?]{"+t.groupSize+"})");s.test(n);)n=(n=n.replace(s,"$1"+t.groupSeparator+"$2")).replace(t.groupSeparator+t.groupSeparator,t.groupSeparator);1<l.length&&(n+=t.radixPoint+l[1])}e.length=n.length;for(var p=0,u=n.length;p<u;p++)e[p]=n.charAt(p);var g=d.inArray("?",e);return a||e.splice(g,1),a?r:g},regex:{number:function(e){var r=d.inputmask.escapeRegex.call(this,e.groupSeparator),a=d.inputmask.escapeRegex.call(this,e.radixPoint),t=isNaN(e.digits)?e.digits:"{0,"+e.digits+"}",i=e.allowPlus||e.allowMinus?"["+(e.allowPlus?"+":"")+(e.allowMinus?"-":"")+"]?":"";return new RegExp("^"+i+"(\\d+|\\d{1,"+e.groupSize+"}(("+r+"\\d{"+e.groupSize+"})?)+)("+a+"\\d"+t+")?$")}},onKeyDown:function(e,r,a){var t=d(this);if(e.keyCode==a.keyCode.TAB){var i=d.inArray(a.radixPoint,r);if(-1!=i){for(var n=t.data("_inputmask").masksets,o=t.data("_inputmask").activeMasksetIndex,l=1;l<=a.digits&&l<a.getMaskLength(n[o]._buffer,n[o].greedy,n[o].repeat,r,a);l++)null!=r[i+l]&&""!=r[i+l]||(r[i+l]="0");this._valueSet(r.join(""))}}else if(e.keyCode==a.keyCode.DELETE||e.keyCode==a.keyCode.BACKSPACE)return a.postFormat(r,0,!0,a),this._valueSet(r.join("")),!0},definitions:{"~":{validator:function(e,r,a,t,i){if(""==e)return!1;if(!t&&a<=1&&"0"===r[0]&&new RegExp("[\\d-]").test(e)&&1==r.join("").length)return r[0]="",{pos:0};var n=t?r.slice(0,a):r.slice();n.splice(a,0,e);var o=n.join(""),l=d.inputmask.escapeRegex.call(this,i.groupSeparator);o=o.replace(new RegExp(l,"g"),"");var s=i.regex.number(i).test(o);if(!(s||(o+="0",s=i.regex.number(i).test(o)))){for(var p=o.lastIndexOf(i.groupSeparator),u=o.length-p;u<=3;u++)o+="0";if(!(s=i.regex.number(i).test(o))&&!t&&e==i.radixPoint&&(s=i.regex.number(i).test("0"+o+"0")))return r[a]="0",{pos:++a}}return 0==s||t||e==i.radixPoint?s:{pos:i.postFormat(r,a,!1,i)}},cardinality:1,prevalidator:null}},insertMode:!0,autoUnmask:!1},integer:{regex:{number:function(e){var r=d.inputmask.escapeRegex.call(this,e.groupSeparator),a=e.allowPlus||e.allowMinus?"["+(e.allowPlus?"+":"")+(e.allowMinus?"-":"")+"]?":"";return new RegExp("^"+a+"(\\d+|\\d{1,"+e.groupSize+"}(("+r+"\\d{"+e.groupSize+"})?)+)$")}},alias:"decimal"}})}(jQuery);
