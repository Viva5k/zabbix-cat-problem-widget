<?php declare(strict_types = 0);

namespace Modules\NewCatProblemWidget\Actions;

use CControllerDashboardWidgetView;
use CControllerResponseData;

class WidgetView extends CControllerDashboardWidgetView {

    protected function doAction(): void {
        // 1. Отримуємо ТІЛЬКИ активні проблеми
        $problems_raw = \API::Problem()->get([
            'output' => ['eventid', 'name', 'severity', 'clock', 'objectid', 'acknowledged'],
            'filter' => ['r_eventid' => 0, 'acknowledged' => 0],
            'recent' => false,
            'sortfield' => 'eventid',
            'sortorder' => 'DESC'
        ]);

        $formatted_problems = [];
        
        if ($problems_raw) {
            $triggerids = array_unique(array_column($problems_raw, 'objectid'));
            
            $triggers = \API::Trigger()->get([
                'output'       => ['triggerid'],
                'triggerids'   => $triggerids,
                'selectHosts'  => ['hostid', 'name'],
                'selectItems'  => ['itemid', 'name'],
                'monitored'    => true,
                'preservekeys' => true
            ]);

            foreach ($problems_raw as $p) {
                if (!array_key_exists($p['objectid'], $triggers)) {
                    continue;
                }

                $formatted_problems[] = [
                    'eventid'   => $p['eventid'],
                    'hostid'    => $triggers[$p['objectid']]['hosts'][0]['hostid'] ?? '',
                    'triggerid' => $p['objectid'],
                    'name'      => $p['name'],
                    'severity'  => (int)$p['severity'],
                    'host'      => $triggers[$p['objectid']]['hosts'][0]['name'] ?? 'Невідомо',
                    'clock'     => $p['clock'],
                    'items'     => array_values($triggers[$p['objectid']]['items'] ?? [])
                ];
            }
        }

        // --- ЛОГІКА КЛІКУ ---
        $fields = $this->getInput('fields', []);
        $cb = isset($fields['cb']) ? (string)$fields['cb'] : '0';
        $seed = floor(time() / 900);

        $this->setResponse(new CControllerResponseData([
            'name' => $this->getInput('name', $this->widget->getName()),
            'problems_data' => $formatted_problems,
            'cat_data' => [
                'choice' => (int)substr($cb, -1) % 2,
                'seed' => $seed,
                'cb' => $cb
            ]
        ]));
    }
}
