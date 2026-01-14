jQuery(function ($) {
  function setSetting(name, value) {
    const $el = $('#' + name);
    $el.val(value).trigger('change');
  }

  function disable($el) { $el.prop('disabled', true).css('opacity', '0.5'); }
  function enable($el)  { $el.prop('disabled', false).css('opacity', '1'); }

  const $machine  = $('#ll_machine_select');
  const $sequence = $('#ll_sequence_select');
  const $level    = $('#ll_level_select');

  // Ao mudar máquina: salva ID e carrega sequences
  $machine.on('change', function () {

    const machineId = $(this).val() || '';

    setSetting('gf_leadlovers_machine', machineId);
    setSetting('gf_leadlovers_sequence', '');
    setSetting('gf_leadlovers_level', '');

    $sequence.html('<option value="">Carregando...</option>');
    $level.html('<option value="">Escolha um Nível</option>');

    disable($sequence);
    disable($level);

    if (!machineId) {
      $sequence.html('<option value="">Escolha uma Sequência</option>');
      return;
    }

    $.post(ajaxurl, {
      action: 'get_leadlovers_sequence_list',
      machine_id: machineId
    }).done(function (result) {
      $sequence.html(result);
      enable($sequence);
    }).fail(function () {
      alert('Erro ao carregar sequências.');
      $sequence.html('<option value="">Escolha uma Sequência</option>');
    });
  });

  // Ao mudar sequência: salva ID e carrega levels
  $sequence.on('change', function () {

    const machineId  = $machine.val() || '';
    const sequenceId = $(this).val() || '';

    setSetting('gf_leadlovers_sequence', sequenceId);
    setSetting('gf_leadlovers_level', '');

    $level.html('<option value="">Carregando...</option>');
    disable($level);

    if (!machineId || !sequenceId) {
      $level.html('<option value="">Escolha um Nível</option>');
      return;
    }

    $.post(ajaxurl, {
      action: 'get_leadlovers_level_list',
      machine_id: machineId,
      sequence_id: sequenceId
    }).done(function (result) {
      $level.html(result);
      enable($level);
    }).fail(function () {
      alert('Erro ao carregar níveis.');
      $level.html('<option value="">Escolha um Nível</option>');
    });
  });

  // Ao mudar nível: salva ID
  $level.on('change', function () {
    setSetting('gf_leadlovers_level', $(this).val() || '');
  });

  // Mostra/esconde as opções de Máquina LeadLovers conforme o checkbox
  $('#gf_leadlovers_dynamic_field_id')
      .on( 'change' , function() {
      if($(this).val()) 
      {
        $( 'div#gform_setting_gf_leadlovers_dynamic_field_text' ).show(); 
        $( 'div#gform_setting_gf_week_dynamic_field_enable' ).show();
        $( 'div#gform_setting_gf_week_dynamic_field_week_plus' ).show();
      }
      else
      {
        $( 'div#gform_setting_gf_leadlovers_dynamic_field_text' ).hide();
        $( 'div#gform_setting_gf_week_dynamic_field_enable' ).hide();
        $( 'div#gform_setting_gf_week_dynamic_field_week_plus' ).hide();
      }
  }).trigger( 'change' );

});


