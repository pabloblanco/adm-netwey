$(document).ready(function () {
    var route = "view/reports/wh_dn/find";

	var dns = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('msisdn'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    prefetch:{
                        url: route,
                        cache: false,
                    },
                    remote: {
                        url: route+'/%QUERY',
                        wildcard: '%QUERY',
                        cache: false
                    }
                });

    var myTypeahead = $('#findDn').typeahead(null, {
        name: 'dns',
        limit: 10,
        minLength: 2,
        display: 'msisdn',
        source: dns
    });

    $('#findDnB').on('click', function(e){
        var dn = $('#findDn').typeahead('val');
        if(dn){
            $.ajax({
                url: "view/reports/wh_dn_detail/"+dn,
                type: 'GET',
                dataType: 'json',
                success: function(result) {
                    if(result.find){
                        $('#dni').text(result.data.dn);
                        $('#whi').text(result.data.whi);
                        $('#pwh').text(result.data.pwh ? result.data.pwh : 'N/A');
                        $('#type').text(result.data.type);

                        if(result.data.usri){
                            $('#usrni').text(result.data.usrni);
                            $('#usri').text(result.data.usri);
                            $('#psell').text(result.data.psell ? result.data.psell : 'N/A');
                            $('#dataSeller').show();
                        }
                        else{
                            $('#dataSeller').hide();
                        }
                        
                        $('#report_container').show();
                    }else
                        alert('No se encontro el dn');
                },
                error: function(){
                    alert('Ocurrio un error mientras se buscaba el dn');
                }
            });
        }else{
            alert('Debe seleccionar un dn');
        }
    });
});