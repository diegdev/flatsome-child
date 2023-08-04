acf.addFilter('acfe/fields/button/data/name=clear', function(data, $el){

    // add custom key
    var row = jQuery($el).parents('.acf-row').data('id');
    data.row = row;
    return data;

});

acf.addAction('acfe/fields/button/success/name=clear', function(response, $el, data){

    // json success was sent
    if(response.success){

        alert('Successfully!');

    }

});
