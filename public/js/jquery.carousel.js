"function"!=typeof Object.create&&(Object.create=function(t){function i(){}return i.prototype=t,new i}),function(s){var i=s.browser.msie&&s.browser.version.substr(0,1)<9,n={settings:{itemsPerPage:1,itemsPerTransition:1,noOfRows:1,pagination:!0,nextPrevLinks:!0,speed:"normal",easing:"swing"},init:function(t,i){return!!t.length&&(this.options=s.extend({},this.settings,i),this.itemIndex=0,this.container=t,this.runner=this.container.find("ul"),this.items=this.runner.children("li"),this.noOfItems=this.items.length,this.setRunnerWidth(),!(this.noOfItems<=this.options.itemsPerPage)&&(this.insertMask(),this.noOfPages=Math.ceil((this.noOfItems-this.options.itemsPerPage)/this.options.itemsPerTransition)+1,this.options.pagination&&this.insertPagination(),this.options.nextPrevLinks&&this.insertNextPrevLinks(),void this.updateBtnStyles()))},insertMask:function(){this.runner.wrap('<div class="mask" />'),this.mask=this.container.find("div.mask");var t=this.runner.outerHeight(!0);this.mask=this.container.find("div.mask"),this.mask.height(t)},setRunnerWidth:function(){this.noOfItems=Math.round(this.noOfItems/this.options.noOfRows);this.items.outerWidth(!0),this.noOfItems;this.runner.width(904)},insertPagination:function(){var t,i=[];for(this.paginationLinks=s('<ol class="pagination-links" />'),t=0;t<this.noOfPages;t++)i[t]='<li><a href="#item-'+t+'">'+(t+1)+"</a></li>";this.paginationLinks.append(i.join("")).appendTo(this.container).find("a").bind("click.carousel",s.proxy(this,"paginationHandler"))},paginationHandler:function(t){return this.itemIndex=t.target.hash.substr(1).split("-")[1]*this.options.itemsPerTransition,this.animate(),!1},insertNextPrevLinks:function(){this.prevLink=s('<a href="#" class="prev">Prev</a>').bind("click.carousel",s.proxy(this,"prevItem")).appendTo(this.container),this.nextLink=s('<a href="#" class="next">Next</a>').bind("click.carousel",s.proxy(this,"nextItem")).appendTo(this.container)},nextItem:function(){return this.itemIndex=this.itemIndex+this.options.itemsPerTransition,this.animate(),!1},prevItem:function(){return this.itemIndex=this.itemIndex-this.options.itemsPerTransition,this.animate(),!1},updateBtnStyles:function(){this.options.pagination&&this.paginationLinks.children("li").removeClass("current").eq(Math.ceil(this.itemIndex/this.options.itemsPerTransition)).addClass("current"),this.options.nextPrevLinks&&(this.nextLink.add(this.prevLink).removeClass("disabled"),this.itemIndex===this.noOfItems-this.options.itemsPerPage?this.nextLink.addClass("disabled"):0===this.itemIndex&&this.prevLink.addClass("disabled"))},animate:function(){var t;this.itemIndex>this.noOfItems-this.options.itemsPerPage&&(this.itemIndex=this.noOfItems-this.options.itemsPerPage),this.itemIndex<0&&(this.itemIndex=0),t=this.items.eq(this.itemIndex).position(),i?this.runner.stop().animate({left:-t.left},this.options.speed,this.options.easing):this.mask.stop().animate({scrollLeft:t.left},this.options.speed,this.options.easing),this.updateBtnStyles()}};s.fn.carousel=function(i){return this.each(function(){var t=Object.create(n);t.init(s(this),i),s.data(this,"carousel",t)})}}(jQuery);
