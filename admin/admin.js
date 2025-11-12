(function($){
  function reindex(){
    $('.fcc-table tbody tr.fcc-row').each(function(i,tr){
      $(tr).find('input,select,textarea').each(function(){
        var name = $(this).attr('name'); if(!name) return;
        name = name.replace(/fast_contact_items\[\d+\]/,'fast_contact_items['+i+']');
        $(this).attr('name', name);
      });
    });
  }
  function defaultIcon(t){
    var p = (window.FCC && FCC.pluginUrl ? FCC.pluginUrl : '') + 'images/';
    t = (t||'').toLowerCase();
    if(t==='zalo') return p+'zalo.png';
    if(t==='messenger') return p+'messenger.png';
    if(t==='hotline' || t==='phone') return p+'phone.png';
    if(t==='email') return p+'email.png';
    if(t==='telegram') return p+'telegram.png';
    return p+'custom.png';
  }
  function refreshRow($row){
    var mode = $row.find('select.icon-mode').val();
    var $btn = $row.find('.choose-img');
    var $hidden = $row.find('input[type=hidden]');
    var $img = $row.find('.icon-preview img');
    if(mode==='custom'){
      $btn.show();
      if($hidden.val()){ $img.attr('src',$hidden.val()); }
    }else{
      $btn.hide();
      $hidden.val('');
      $img.attr('src', defaultIcon($row.find('select.type').val()));
    }
  }
  function bindMedia($btn){
    var frame = wp.media({ title:'Chọn ảnh', multiple:false, library:{type:'image'} });
    frame.on('select', function(){
      var at = frame.state().get('selection').first().toJSON();
      var $row = $btn.closest('tr.fcc-row');
      $row.find('input[type=hidden]').val(at.url);
      $row.find('.icon-preview img').attr('src', at.url);
    });
    frame.open();
  }
  function addRow(){
    var i = $('.fcc-table tbody tr.fcc-row').length;
    var row = `
    <tr class="fcc-row">
      <td class="drag">☰</td>
      <td>
        <div class="icon-ctl">
          <select name="fast_contact_items[${i}][icon_mode]" class="icon-mode">
            <option value="default">Icon có sẵn</option>
            <option value="custom">Tùy chỉnh</option>
          </select>
          <button class="button choose-img" type="button" style="display:none;">Chọn ảnh</button>
          <input type="hidden" name="fast_contact_items[${i}][img]" value="">
        </div>
      </td>
      <td class="icon-preview-col"><div class="icon-preview"><img src="${defaultIcon('Zalo')}" alt="icon"></div></td>
      <td>
        <select name="fast_contact_items[${i}][type]" class="type">
          <option>Zalo</option><option>Messenger</option><option>Hotline</option>
          <option>Email</option><option>Telegram</option><option>Custom</option>
        </select>
      </td>
      <td><input type="text" name="fast_contact_items[${i}][label]" value=""></td>
      <td><input type="text" name="fast_contact_items[${i}][value]" value=""></td>
      <td><input type="text" class="fcc-color" name="fast_contact_items[${i}][bg]" value="#1e88e5" data-default-color="#1e88e5"></td>
      <td class="pos">
        <label><input type="radio" name="fast_contact_items[${i}][position]" value="left"> Trái</label>
        <label><input type="radio" name="fast_contact_items[${i}][position]" value="right" checked> Phải</label>
      </td>
      <td><button class="button remove-row" type="button">X</button></td>
    </tr>`;
    $('.fcc-table tbody').append(row);
    $('.fcc-color').wpColorPicker();
  }

  $(document).on('click','#add-row', function(e){ e.preventDefault(); addRow(); });
  $(document).on('click','.remove-row', function(){ $(this).closest('tr').remove(); reindex(); });
  $(document).on('change','select.icon-mode, select.type', function(){ refreshRow($(this).closest('tr.fcc-row')); });
  $(document).on('click','.choose-img', function(e){ e.preventDefault(); bindMedia($(this)); });
  $(function(){
    $('.fcc-color').wpColorPicker();
    $('.sortable').sortable({ handle:'.drag', update:reindex });
    $('.fcc-table tbody tr.fcc-row').each(function(){ refreshRow($(this)); });
  });
})(jQuery);
