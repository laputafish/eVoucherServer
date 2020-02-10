<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Menu;

class MenuController extends BaseController
{
  public $menu = [
      [
        'labelTag' => 'menu.vouchers',
        'type' => 'link',
        'routeName' => 'vouchers',
        'iconClass' => 'fas fa-qrcode',
        'link' => '/vouchers',
      ],
      [
        'labelTag' => 'menu.agents',
        'type' => 'link',
        'routeName' => 'agents',
        'iconClass' => 'fas fa-chess',
        'link' => '/agents',
      ],
      [
        'labelTag' => 'menu.dashboard',
        'type' => 'group',
        'iconClass' => 'fas fa-tachometer-alt',
        'children' =>
          [
              [
                'labelTag' => 'menu.dashboard1',
                'type' => 'link',
                'routeName' => 'dashboard1',
                'iconClass' => 'far fa-circle',
                'link' => '/dashboard',
              ],
              [
                'labelTag' => 'menu.dashboard2',
                'type' => 'link',
                'routeName' => 'dashboard2',
                'iconClass' => 'far fa-circle',
                'link' => '/dashboard2',
              ],
              [
                'labelTag' => 'menu.dashboard3',
                'type' => 'link',
                'routeName' => 'dashboard3',
                'iconClass' => 'far fa-circle',
                'link' => '/dashboard3',
              ],
          ],
      ],
      [
        'labelTag' => 'menu.widgets',
        'type' => 'link',
        'routeName' => 'widgets',
        'iconClass' => 'fas fa-th',
        'badge' => 'New',
        'badgeVariant' => 'danger',
        'link' => '/widgets',
      ],
      [
        'labelTag' => 'menu.layout_options',
        'type' => 'group',
        'iconClass' => 'fas fa-copy',
        'badge' => '6',
        'badgeVariant' => 'info',
        'children' =>
          [
              [
                'labelTag' => 'menu.top_navigation',
                'type' => 'link',
                'routeName' => 'top_navigation',
                'iconClass' => 'far fa-circle',
                'link' => '/top_navigation',
              ],
              [
                'labelTag' => 'menu.top_navigation_sidebar',
                'type' => 'link',
                'routeName' => 'top_navigation_sidebar',
                'iconClass' => 'far fa-circle',
                'link' => '/top_navigation_sidebar',
              ],
              [
                'labelTag' => 'menu.boxed',
                'type' => 'link',
                'routeName' => 'boxed',
                'iconClass' => 'far fa-circle',
                'link' => '/boxed',
              ],
              [
                'labelTag' => 'menu.fixed_sidebar',
                'type' => 'link',
                'routeName' => 'fixed_sidebar',
                'iconClass' => 'far fa-circle',
                'link' => '/fixed_sidebar',
              ],
              [
                'labelTag' => 'menu.fixed_navbar',
                'type' => 'link',
                'routeName' => 'fixed_navbar',
                'iconClass' => 'far fa-circle',
                'link' => '/fixed_navbar',
              ],
              [
                'labelTag' => 'menu.fixed_footer',
                'type' => 'link',
                'routeName' => 'fixed_footer',
                'iconClass' => 'far fa-circle',
                'link' => '/fixed_footer',
              ],
              [
                'labelTag' => 'menu.collapsed_sidebar',
                'type' => 'link',
                'routeName' => 'collapsed_sidebar',
                'iconClass' => 'far fa-circle',
                'link' => '/collapsed_sidebar',
              ],
          ],
      ],
      [
        'labelTag' => 'menu.charts',
        'type' => 'group',
        'iconClass' => 'fas fa-chart-pie',
        'children' =>
          [
              [
                'labelTag' => 'menu.charts_js',
                'type' => 'link',
                'routeName' => 'charts_js',
                'iconClass' => 'far fa-circle',
                'link' => '/charts_js',
              ],
              [
                'labelTag' => 'menu.flot',
                'type' => 'link',
                'routeName' => 'charts_flot',
                'iconClass' => 'far fa-circle',
                'link' => '/flot',
              ],
              [
                'labelTag' => 'menu.inline',
                'type' => 'link',
                'routeName' => 'charts_inline',
                'iconClass' => 'far fa-circle',
                'link' => '/inline',
              ],
          ],
      ],
      [
        'labelTag' => 'menu.ui_elements',
        'type' => 'group',
        'iconClass' => 'fas fa-tree',
        'children' =>
          [
              [
                'labelTag' => 'menu.general',
                'type' => 'link',
                'routeName' => 'ui_general',
                'iconClass' => 'fas fa-circle',
                'link' => '/ui_general',
              ],
              [
                'labelTag' => 'menu.icons',
                'type' => 'link',
                'routeName' => 'ui_icons',
                'iconClass' => 'fas fa-circle',
                'link' => '/ui_icons',
              ],
              [
                'labelTag' => 'menu.buttons',
                'type' => 'link',
                'routeName' => 'ui_buttons',
                'iconClass' => 'fas fa-circle',
                'link' => '/ui_buttons',
              ],
              [
                'labelTag' => 'menu.sliders',
                'type' => 'link',
                'routeName' => 'ui_sliders',
                'iconClass' => 'fas fa-circle',
                'link' => '/ui_sliders',
              ],
              [
                'labelTag' => 'menu.modals_alerts',
                'type' => 'link',
                'iconClass' => 'fas fa-circle',
                'link' => '/ui_modals_alerts',
              ],
              [
                'labelTag' => 'menu.navbar_tabs',
                'type' => 'link',
                'routeName' => 'ui_navbar_tabs',
                'iconClass' => 'fas fa-circle',
                'link' => '/ui_navbar_tabs',
              ],
              [
                'labelTag' => 'menu.timeline',
                'type' => 'link',
                'routeName' => 'ui_timeline',
                'iconClass' => 'fas fa-circle',
                'link' => '/ui_timeline',
              ],
              [
                'labelTag' => 'menu.ribbons',
                'type' => 'link',
                'routeName' => 'ui_ribbons',
                'iconClass' => 'fas fa-circle',
                'link' => '/ui_ribbons',
              ],
          ],
      ],
      [
        'labelTag' => 'menu.forms',
        'type' => 'group',
        'iconClass' => 'far fa-circle',
        'children' =>
          [
              [
                'labelTag' => 'menu.general',
                'type' => 'link',
                'routeName' => 'forms_general',
                'iconClass' => 'far fa-circle',
                'link' => '/forms/general',
              ],
              [
                'labelTag' => 'menu.advanced',
                'type' => 'link',
                'routeName' => 'forms_advanced',
                'iconClass' => 'far fa-circle',
                'link' => '/forms/advanced',
              ],
              [
                'labelTag' => 'menu.editors',
                'type' => 'link',
                'routeName' => 'forms_editors',
                'iconClass' => 'far fa-circle',
                'link' => '/forms/editors',
              ],
              [
                'labelTag' => 'menu.validation',
                'type' => 'link',
                'routeName' => 'forms_validation',
                'iconClass' => 'far fa-circle',
                'link' => '/forms/validation',
              ],
          ],
      ],
      [
        'labelTag' => 'menu.tables',
        'type' => 'group',
        'iconClass' => 'fas fa-table',
        'children' =>
          [
              [
                'labelTag' => 'menu.simple_tables',
                'type' => 'link',
                'routeName' => 'simple_tables',
                'iconClass' => 'far fa-circle',
                'link' => '/tables/simple_table',
              ],
              [
                'labelTag' => 'menu.datatables',
                'type' => 'link',
                'routeName' => 'datatables',
                'iconClass' => 'far fa-circle',
                'link' => '/tables/datatables',
              ],
              [
                'labelTag' => 'menu.js_grid',
                'type' => 'link',
                'routeName' => 'js_grid',
                'iconClass' => 'far fa-circle',
                'link' => '/tables/js_grid',
              ],
          ],
      ],
  ];

  public function makeMenu()
  {
    $root = Menu::whereType('root')->first();
    if (is_null($root)) {
      $root = new Menu([
        'type' => 'root'
      ]);
      $root->save();
    }

    $labelTags = [];
    foreach($this->menu as $menu) {
      $this->attachMenu($root, $menu, $labelTags);
    }

    echo 'Label Tags (Obsolate)'.PHP_EOL;
    echo '****************************************'.PHP_EOL;
    $obsolateRows = Menu::whereNotIn('label_tag', $labelTags)->where('label_tag', '<>', 'root')->get();
    foreach($obsolateRows as $row) {
      echo $row['label_tag'].' => deleted.'.PHP_EOL;
      $row->delete();
    }
    echo '****************************************'.PHP_EOL;


    return response()->json('ok');
  }

  private function attachMenu($parent, $menu, &$labelTags) {
    $node = $parent->descendants()->where('label_tag', $menu['labelTag'])->first();
    echo 'label_tag = '.$menu['labelTag'].PHP_EOL;
    if (is_null($node)) {
      echo ' is null'.PHP_EOL;
      $node = new Menu([
        'type' => $menu['type'],
        'icon_class' => $menu['iconClass'],
        'route_name' => array_key_exists('routeName', $menu) ? $menu['routeName'] : '',
        'label_tag' => $menu['labelTag'],
        'link' => array_key_exists('link', $menu) ? $menu['link'] : ''
      ]);
      $node->appendToNode($parent)->save();
    } else {
      $node->icon_class = $menu['iconClass'];
      $node->link = array_key_exists('link', $menu) ? $menu['link'] : '';
      $node->route_name = array_key_exists('routeName', $menu) ? $menu['routeName'] : '';
      $node->save();
    }

    if ($menu['type'] == 'group') {
      foreach ($menu['children'] as $childMenu) {
        $this->attachMenu($node, $childMenu, $labelTags);
      }
    }

    if (!in_array($menu['labelTag'], $labelTags)) {
      $labelTags[] = $menu['labelTag'];
    }
  }

  public function index()
  {
    $root = Menu::whereType('root')->first();

    $nodes = Menu::descendantsOf($root->id)->where('enabled', 1)->toTree();
    return response()->json([
      'status' => true,
      'result' => $nodes
    ]);
  }
}