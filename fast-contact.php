<?php
/*
Plugin Name: Fast Contact
Description: Công cụ hỗ trợ thêm nút liên hệ cho các trang wordpress (Messenger, Zalo, Hotline, ...)
Version: 1.0.0
Author: vhduongnt@gmail.com
*/

if (!defined('ABSPATH')) exit;

class FastContactClean {
  private $opt = 'fast_contact_items';

  function __construct(){
    add_action('admin_menu', [$this,'menu']);
    add_action('admin_enqueue_scripts', [$this,'admin_assets']);
    add_action('admin_init', [$this,'register']);
    add_action('wp_enqueue_scripts', [$this,'front_assets']);
    add_action('wp_footer', [$this,'render_front']);
  }

  function register(){
    register_setting('fast_contact_group', $this->opt, [
      'type' => 'array',
      'sanitize_callback' => [$this,'sanitize']
    ]);
  }

  function sanitize($items){
    $out = [];
    if (!is_array($items)) return $out;
    foreach ($items as $it){
      $out[] = [
        'icon_mode' => in_array($it['icon_mode']??'default',['default','custom']) ? $it['icon_mode'] : 'default',
        'img'       => esc_url_raw($it['img'] ?? ''),
        'type'      => sanitize_text_field($it['type'] ?? 'Zalo'),
        'label'     => sanitize_text_field($it['label'] ?? ''),
        'value'     => sanitize_text_field($it['value'] ?? ''),
        'bg'        => sanitize_hex_color($it['bg'] ?? '#1e88e5') ?: '#1e88e5',
        'position'  => in_array($it['position']??'right',['left','right']) ? $it['position'] : 'right',
      ];
    }
    return $out;
  }

  function menu(){
    add_menu_page('Fast Contact', 'Fast Contact', 'manage_options', 'fast-contact-clean', [$this,'page'], 'dashicons-share', 80);
  }

  function admin_assets($hook){
    if (strpos($hook,'fast-contact-clean')===false) return;
    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_style('fcc-admin', plugin_dir_url(__FILE__).'admin/admin.css', [], '1.0.0');
    wp_enqueue_script('fcc-admin', plugin_dir_url(__FILE__).'admin/admin.js', ['jquery','wp-color-picker','jquery-ui-sortable'], '1.0.0', true);
    wp_localize_script('fcc-admin','FCC', ['pluginUrl'=>plugin_dir_url(__FILE__)]);
  }

  function page(){
    $items = get_option($this->opt, []);
    ?>
    <div class="wrap">
      <h1>Fast Contact</h1>
      <p class="description">Thêm nút liên hệ, chọn vị trí hiển thị và màu nền. Kéo thả để sắp xếp.</p>
      <form method="post" action="options.php">
        <?php settings_fields('fast_contact_group'); ?>
        <table class="widefat fcc-table">
          <thead>
            <tr>
              <th style="width:40px">#</th>
              <th style="width:260px">Ảnh/Icon & Loại</th>
              <th style="width:90px;text-align:center;">Icon</th>
              <th style="width:140px">Loại</th>
              <th>Label</th>
              <th style="width:260px">Giá trị</th>
              <th style="width:160px">Màu nền</th>
              <th style="width:160px">Vị trí</th>
              <th style="width:60px">Xóa</th>
            </tr>
          </thead>
          <tbody class="sortable">
          <?php foreach($items as $i=>$it): ?>
            <tr class="fcc-row">
              <td class="drag">☰</td>
              <td>
                <div class="icon-ctl">
                  <select name="<?php echo $this->opt; ?>[<?php echo $i; ?>][icon_mode]" class="icon-mode">
                    <option value="default" <?php selected('default',$it['icon_mode']??'default'); ?>>Icon có sẵn</option>
                    <option value="custom" <?php selected('custom',$it['icon_mode']??'default'); ?>>Tùy chỉnh</option>
                  </select>
                  <button class="button choose-img" type="button">Chọn ảnh</button>
                  <input type="hidden" name="<?php echo $this->opt; ?>[<?php echo $i; ?>][img]" value="<?php echo esc_attr($it['img']??''); ?>">
                </div>
              </td>
              <td class="icon-preview-col">
                <?php
                  $src = ($it['icon_mode']??'default')==='custom' && !empty($it['img']) ? esc_url($it['img']) : esc_url($this->map_icon($it['type']??'Zalo'));
                ?>
                <div class="icon-preview"><img src="<?php echo $src; ?>" alt="icon"></div>
              </td>
              <td>
                <select name="<?php echo $this->opt; ?>[<?php echo $i; ?>][type]" class="type">
                  <?php foreach(['Zalo','Messenger','Hotline','Email','Telegram','Custom'] as $t): ?>
                  <option value="<?php echo esc_attr($t); ?>" <?php selected($t,$it['type']??'Zalo'); ?>><?php echo esc_html($t); ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><input type="text" name="<?php echo $this->opt; ?>[<?php echo $i; ?>][label]" value="<?php echo esc_attr($it['label']??''); ?>"></td>
              <td><input type="text" name="<?php echo $this->opt; ?>[<?php echo $i; ?>][value]" value="<?php echo esc_attr($it['value']??''); ?>"></td>
              <td><input type="text" class="fcc-color" name="<?php echo $this->opt; ?>[<?php echo $i; ?>][bg]" value="<?php echo esc_attr($it['bg']??'#1e88e5'); ?>" data-default-color="#1e88e5"></td>
              <td class="pos">
                <label><input type="radio" name="<?php echo $this->opt; ?>[<?php echo $i; ?>][position]" value="left" <?php checked('left',$it['position']??'right'); ?>> Trái</label>
                <label><input type="radio" name="<?php echo $this->opt; ?>[<?php echo $i; ?>][position]" value="right" <?php checked('right',$it['position']??'right'); ?>> Phải</label>
              </td>
              <td><button class="button remove-row" type="button">X</button></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <p><button class="button button-secondary" id="add-row" type="button">+ Thêm</button></p>
        <?php submit_button('Lưu lại'); ?>
      </form>
    </div>
    <?php
  }

  function map_icon($type){
    $base = plugin_dir_url(__FILE__).'images/';
    $t = strtolower($type);
    if ($t==='zalo') return $base.'zalo.png';
    if ($t==='messenger') return $base.'messenger.png';
    if ($t==='hotline' || $t==='phone') return $base.'phone.png';
    if ($t==='email') return $base.'email.png';
    if ($t==='telegram') return $base.'telegram.png';
    return $base.'custom.png';
  }

  function front_assets(){
    $items = get_option($this->opt, []);
    if (empty($items)) return;
    wp_enqueue_style('fcc-front', plugin_dir_url(__FILE__).'assets/style.css', [], '1.0.0');
  }

  function build_link($type,$value){
    switch($type){
      case 'Zalo': return 'https://zalo.me/'.ltrim($value,'/');
      case 'Messenger': return 'https://m.me/'.ltrim($value,'/');
      case 'Hotline': return 'tel:'.preg_replace('/\s+/','',$value);
      case 'Email': return 'mailto:'.$value;
      case 'Telegram': return 'https://t.me/'.ltrim($value,'@/');
      default: return esc_url($value);
    }
  }

  function render_front(){
    $items = get_option($this->opt, []);
    if (empty($items)) return;
    $offset = ['left'=>20,'right'=>20];
    echo '<div class="fcc-wrap">';
    foreach ($items as $it){
      $type = $it['type'] ?? 'Zalo';
      $bg   = sanitize_hex_color($it['bg'] ?? '#1e88e5') ?: '#1e88e5';
      $href = esc_url($this->build_link($type, $it['value'] ?? ''));
      $pos  = ($it['position'] ?? 'right') === 'left' ? 'left' : 'right';
      $icon = ($it['icon_mode'] ?? 'default') === 'custom' && !empty($it['img']) ? esc_url($it['img']) : esc_url($this->map_icon($type));
      $style = "background:$bg;position:fixed;{$pos}:20px;bottom:{$offset[$pos]}px;";
      $offset[$pos] += 66; // 56 button + 10 gap
      printf('<a class="fcc-btn fcc-%s" href="%s" target="_blank" rel="nofollow noopener" style="%s"><img src="%s" alt="%s"></a>',
        esc_attr($pos), $href, esc_attr($style), $icon, esc_attr($type)
      );
    }
    echo '</div>';
  }
}

new FastContactClean();
