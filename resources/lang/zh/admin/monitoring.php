<?php

return [
    'navigation' => [
        'label' => '监控',
        'group' => '管理',
    ],

    'page' => [
        'title' => '监控',
        'heading' => '实时监控',
    ],

    'actions' => [
        'refresh' => '刷新数据',
    ],

    'selector' => [
        'label' => '选择节点',
        'placeholder' => '选择一个节点……',
    ],

    'stats' => [
        'cpu_usage' => 'CPU使用率',
        'cpu_cores' => ':count 个可用核心',
        'memory_usage' => '内存使用量',
        'disk_usage' => '磁盘使用量',
        'network_traffic' => '网络流量',
        'uptime' => '运行时间',
        'goroutines' => ':count 个协程',
        'last_updated' => '最后更新',
        'no_node' => '未选择节点',
        'no_node_desc' => '请选择一个节点以查看监控数据',
        'no_node_hint' => '请使用上方的下拉菜单',
        'error' => '错误',
        'error_desc' => '无法加载监控数据',
        'error_fetch' => '无法从代理获取数据',
        'error_node_gone' => '节点不再存在',
    ],

    'details' => [
        'heading' => '系统详情',
        'button' => '详情',
        'close' => '关闭',
        'no_data' => '无可用数据。请确保节点在线。',

        'cpu_section' => 'CPU',
        'cpu_total' => '总使用率',
        'cpu_cores' => '核心数',
        'per_core' => '每核心使用率',

        'memory_section' => '内存',
        'total_memory' => '总计',
        'used_memory' => '已使用',
        'free_memory' => '空闲',
        'available_memory' => '可用',

        'swap_section' => '交换空间',
        'swap_none' => '此节点未配置交换空间。',
        'swap_total' => '总计',
        'swap_used' => '已使用',
        'swap_free' => '空闲',
        'swap_usage' => '使用率',

        'network_section' => '网络',
        'bytes_sent' => '发送字节数',
        'bytes_recv' => '接收字节数',
        'packets_sent' => '发送数据包',
        'packets_received' => '收到的数据包',

        'runtime_section' => '运行时',
        'go_version' => '去版本',
        'arch' => '建筑学',
        'goroutines' => 'Goroutine',
        'uptime' => '正常运行时间',
    ],
    'servers' => [
        'heading' => '服务器使用情况',
        'no_node' => '选择一个节点以查看服务器使用情况。',
        'no_servers' => '在此节点上找不到服务器。',
        'error_fetch' => '无法从代理获取服务器数据。',
        'col' => [
            'name' => '服务器',
            'state' => '状态',
            'cpu' => 'CPU',
            'memory' => '记忆',
            'disk' => '磁盘',
            'network' => '网络',
            'uptime' => '正常运行时间',
        ],
        'states' => [
            'running' => '跑步',
            'starting' => '开始',
            'stopping' => '停止',
            'offline' => '离线',
            'crashed' => '坠毁',
            'unknown' => '未知',
        ],
    ],
];
