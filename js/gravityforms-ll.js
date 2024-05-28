/*
jQuery(document).ready(function ($) 
{
    // Evento disparado ao mudar o conteúdo do select MACHINE
    // Atualiza o campo de sequences conforme a nova escolha
    // Capturando por ajax na função get_leadlovers_sequence_list
    // O retorno já é no formato html <option value='115'>Nome da máquina</option>
    $('#gf_leadlovers_machine').on('change', function()
    {
        $('#gf_leadlovers_sequence').prop('disabled', true);
        $('#gf_leadlovers_sequence').css('opacity', '0.5');
        $('#gf_leadlovers_level').prop('disabled', true);
        $('#gf_leadlovers_level').css('opacity', '0.5');

        var selectedValue = $(this).val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                'action': 'get_leadlovers_sequence_list',
                'machine_id': selectedValue,
            },
            success: function(result){
                // alert(result);
                $('#gf_leadlovers_sequence').html(result);
                $('#gf_leadlovers_sequence').prop('disabled', false);
                $('#gf_leadlovers_sequence').css('opacity', '1');
                },
            error: function(result){
                alert('Erro no ajax!!');
            }
        });


    });
    // Evento disparado ao mudar o conteúdo do select SEQUENCE

    $('#gf_leadlovers_sequence').on('change', function()
    {
        $('#gf_leadlovers_level').prop('disabled', true);
        $('#gf_leadlovers_level').css('opacity', '0.5');
     
        var selectedValue = $(this).val();
        // alert(selectedValue + ' e ' + $('#gf_leadlovers_machine'));
        $.ajax({
            url: ajaxurl,
            type: 'POST',        //Não consegui usar modo POST, não sei pq...
            dataType: 'json',
            data: {
                'action': 'get_leadlovers_level_list',
                'sequence_id': selectedValue,
                'machine_id' : $('#gf_leadlovers_machine').val()
            },
            success: function(result){
                // alert(result);
                 $('#gf_leadlovers_level').html(result);
                 $('#gf_leadlovers_level').prop('disabled', false);
                 $('#gf_leadlovers_level').css('opacity', '1');
            },
            error: function(result){
                alert('Erro no ajax!');
            }
        });
    });
});




*/