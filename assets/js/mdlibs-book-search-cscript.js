jQuery(document).ready(function(){
    //jQuery('input').popup();
   
});
jQuery(document).on('click','#mdlibsearch', function(e){
    e.preventDefault();
    let mdlibs_bookname, mdlibsAuthor, mdlibsPublisher, mdlibsrating, mdlibs_range1, mdlibs_range2, mdlibs_nonce;
    mdlibs_bookname = jQuery(this).parents('.mdlibsInnerdiv').find('#mdlibsBookName').val();
    mdlibsAuthor = jQuery(this).parents('.mdlibsInnerdiv').find('#mdlibsAuthor').val();
    mdlibsPublisher = jQuery(this).parents('.mdlibsInnerdiv').find('#mdlibsPublisher').val();
    mdlibsrating = jQuery(this).parents('.mdlibsInnerdiv').find('#mdlibsrating').val();
    mdlibs_range1 = jQuery(this).parents('.mdlibsInnerdiv').find('#mdlibs-range-1a').val();
    mdlibs_range2 = jQuery(this).parents('.mdlibsInnerdiv').find('#mdlibs-range-1b').val();
    mdlibs_nonce = jQuery(this).parents('.mdlibsInnerdiv').find('#mdlibs_nonce').val();    
    bindThis = jQuery(this);
    jQuery.ajax({
        url: mdlibsAjax.ajaxurl,
        type: "post",
        dataType: "json",
        data: {action: "mdlibs_ajax_filter_callback", bookname: mdlibs_bookname, bookauthor: mdlibsAuthor, bookpublisher: mdlibsPublisher, bookrating: mdlibsrating, bookrange1: mdlibs_range1, bookrange2: mdlibs_range2, mdlibsnonce: mdlibs_nonce },
        beforeSend: function(){
            bindThis.parents('.mdlibsInnerCenter').find('#mdlibsloader').css("display", "block");
        },
        success: function(res){            
            if(res.success == true){
                bindThis.parents('.mdlibsmain').find('#mdlibSearchResult').html(res.data);
            }            
        },
        complete:function(data){
            bindThis.parents('.mdlibsInnerCenter').find('#mdlibsloader').css("display", "none");
        }
    });
}); 