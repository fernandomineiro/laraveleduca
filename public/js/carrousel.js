function setup_galeria(e,i,t,r){montaVisualizacao(e,i,t,r),jQuery(i+" a.next").click(function(){next(e,i,t,r)}),jQuery(i+" a.prev").click(function(){previous(e,i,t,r)})}function montaVisualizacao(t,e,i,r){jQuery(e+" > "+i).hide(),jQuery(e+" > "+i).each(function(e,i){jQuery(this).attr("id",t+"_item_"+e),e<r&&jQuery("#"+t+"_item_"+e).show()})}function previous(e,i,t,r){if(jQuery(i+" > "+t).length>r){var u="#"+jQuery(i+" > "+t+":visible:first").prev().attr("id");console.log(u),jQuery(u).length&&(jQuery(i+" > "+t+":visible:last").hide(),jQuery(u).fadeIn())}}function next(e,i,t,r){if(jQuery(i+" > "+t).length>r){var u="#"+jQuery(i+" > "+t+":visible:last").next().attr("id");jQuery(u).length&&(jQuery(i+" > "+t+":visible:first").hide(),jQuery(u).fadeIn())}}
