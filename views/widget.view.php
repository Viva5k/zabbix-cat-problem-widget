<?php declare(strict_types = 0);

$problems = $data['problems_data'];
$container = (new \CDiv())->addClass('cat-problem-container');

if (empty($problems)) {
    $cat_info = $data['cat_data'];
    $url = ($cat_info['choice'] === 0) 
        ? "https://cataas.com/cat?t=" . $cat_info['seed'] . "&cb=" . $cat_info['cb']
        : "https://placedog.net/500/500?random=" . $cat_info['seed'] . "&cb=" . $cat_info['cb'];
    $container->addStyle('cursor: pointer;');
    $bg = (new \CDiv())->addStyle('position:absolute; width:100%; height:100%; background:url("'.$url.'") center/cover; filter:blur(15px) brightness(0.5); transform:scale(1.1);');
    $img = (new \CImg($url))->addStyle('position:relative; width:100%; height:100%; object-fit:contain; z-index:2;');
    $container->addItem([$bg, $img]);
} else {
    $colors = [0=>'#97AAB3', 1=>'#7499FF', 2=>'#FFC859', 3=>'#FFA059', 4=>'#E97659', 5=>'#E45959'];
    $names = [0=>'N/A', 1=>'Інфо', 2=>'Увага', 3=>'Середня', 4=>'Висока', 5=>'Надзвичайна'];

    $table = (new \CTableInfo())->setHeader([
        (new \CColHeader('Час'))->addStyle('width: 10%;'),
        (new \CColHeader('Важливість'))->addStyle('width: 10%;'),
        (new \CColHeader('Вузол'))->addStyle('width: 20%;'),
        (new \CColHeader('Проблема'))->addStyle('width: 50%;'),
        (new \CColHeader('Дія'))->addStyle('width: 10%;')
    ]);

    foreach ($problems as $p) {
        $color = $colors[$p['severity']];
        $sev_badge = (new \CSpan($names[$p['severity']]))
            ->addStyle("background:$color; color:#000; padding:2px 5px; border-radius:3px; font-weight:normal; font-size:10px;");

        $is_new = (time() - (int)$p['clock']) < 15;
        $blink_class = $is_new ? 'blink-new' : '';

        $btn = (new \CButton(null, 'ОНОВИТИ'))
            ->addClass('btn-alt')
            ->onClick("PopUp('acknowledge.edit', {eventids: ['".$p['eventid']."']}, {dialogue_class: 'modal-popup-medium'});");

        $menu_data = \CMenuPopupHelper::getTrigger([
            'triggerid' => $p['triggerid'],
            'eventid' => $p['eventid'],
            'backurl' => 'zabbix.php?action=dashboard.view'
        ]);

        $table->addRow([
            (new \CCol(date('H:i:s', (int)$p['clock'])))->addStyle('color:#fff; vertical-align:middle;'),
            (new \CCol($sev_badge))->addStyle('vertical-align:middle;'),
            (new \CCol($p['host']))->addStyle('color:#fff; font-weight:normal; vertical-align:middle;'),
            (new \CCol($p['name']))
                ->addClass('severity-cell')
                ->addClass('problem-name-link')
                ->addClass('js-menu-popup')
                ->addClass($blink_class)
                ->setAttribute('data-menu-popup', json_encode($menu_data))
                ->addStyle("background:$color; color:#000; font-weight:normal; vertical-align:middle; --sev-color:$color; cursor:pointer;"),
            (new \CCol($btn))->addStyle('vertical-align:middle;')
        ]);
    }
    $container->addItem($table);
}

(new \CWidgetView($data))->addItem($container)->show();
