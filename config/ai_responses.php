<?php

return [
    'is_fake_response_enabled' => env('IS_FAKE_RESPONSES_ENABLED', false),

    'fake_responses' => [

        'success' => [
            'output' => json_decode(<<<'END'
[
  {
    "ui_type": "text",
    "title": "Assistant advice",
    "description": "AI-generated explanation or recommendation",
    "data": {
      "content": "I prepared a dataset summary, a revenue trend chart, a regional pie chart, and a Mermaid diagram describing the system flow."
    }
  },
  {
    "ui_type": "table",
    "title": "Statistics",
    "description": "Statistics by country",
    "data": {
      "columns": [
        {
          "key": "country",
          "label": "Country"
        },
        {
          "key": "users",
          "label": "Users"
        },
        {
          "key": "revenue",
          "label": "Revenue ($)"
        },
        {
          "key": "growth",
          "label": "Growth %"
        }
      ],
      "rows": [
        {
          "country": "United States",
          "users": 12500,
          "revenue": 48000,
          "growth": 12.4
        },
        {
          "country": "Germany",
          "users": 8300,
          "revenue": 31000,
          "growth": 9.1
        },
        {
          "country": "India",
          "users": 21400,
          "revenue": 22000,
          "growth": 18.7
        }
      ]
    }
  },
  {
    "ui_type": "stacked-area-chart",
    "title": "Traffic Trend",
    "description": "Weekly Traffic Trend",
    "data": {
      "x_axis": {
        "values": [
          "Mon",
          "Tue",
          "Wed",
          "Thu",
          "Fri",
          "Sat",
          "Sun"
        ]
      },
      "series": [
        {
          "name": "Email",
          "data": [
            120,
            132,
            101,
            134,
            90,
            230,
            210
          ]
        },
        {
          "name": "Union Ads",
          "data": [
            220,
            182,
            191,
            234,
            290,
            330,
            310
          ]
        },
        {
          "name": "Video Ads",
          "data": [
            150,
            232,
            201,
            154,
            190,
            330,
            410
          ]
        },
        {
          "name": "Direct",
          "data": [
            320,
            332,
            301,
            334,
            390,
            330,
            320
          ]
        },
        {
          "name": "Search Engine",
          "data": [
            820,
            932,
            901,
            934,
            1290,
            1330,
            1320
          ]
        }
      ]
    }
  },
  {
    "ui_type": "line-chart",
    "title": "Product Sales Trend",
    "description": "Annual sales by product (2012-2017)",
    "data": {
      "x_axis": {
        "values": [
          "2012",
          "2013",
          "2014",
          "2015",
          "2016",
          "2017"
        ]
      },
      "series": [
        {
          "name": "Milk Tea",
          "data": [
            56.5,
            82.1,
            88.7,
            70.1,
            53.4,
            85.1
          ]
        },
        {
          "name": "Matcha Latte",
          "data": [
            51.1,
            51.4,
            55.1,
            53.3,
            73.8,
            68.7
          ]
        },
        {
          "name": "Cheese Cocoa",
          "data": [
            40.1,
            62.2,
            69.5,
            36.4,
            45.2,
            32.5
          ]
        },
        {
          "name": "Walnut Brownie",
          "data": [
            25.2,
            37.1,
            41.2,
            18,
            33.9,
            49.1
          ]
        }
      ]
    }
  },
  {
    "ui_type": "pie_chart",
    "title": "Web Traffic Pie Chart",
    "data": {
      "series": [
        {
          "name": "Search Engine",
          "value": 1048
        },
        {
          "name": "Direct",
          "value": 735
        },
        {
          "name": "Email",
          "value": 580
        },
        {
          "name": "Union Ads",
          "value": 484
        },
        {
          "name": "Video Ads",
          "value": 300
        }
      ]
    }
  },
  {
    "ui_type": "mermaid",
    "title": "System Flow",
    "description": "Mermaid flowchart: how data moves between services",
    "data": {
      "syntax": "\nflowchart LR\n  U[User] -->|Prompt / Click| UI[Web UI]\n  UI -->|POST /chat| API[Laravel API]\n  API -->|Webhook trigger| N8N[n8n Workflow]\n  N8N -->|Generate UI blocks| LLM[LLM]\n  N8N -->|Read/Write context| DB[(Database)]\n  N8N -->|Return UI response| API\n  API -->|Render blocks| UI\n"
    }
  },
  {
    "ui_type": "code_editor",
    "title": "Example: Laravel Controller snippet",
    "description": "Returning UI blocks from backend",
    "data": {
      "language": "php",
      "filename": "ChatController.php",
      "highlight_lines": [
        5,
        6,
        7
      ],
      "code": "<?php\n\nreturn response()->json([\n  'blocks' => 'uiBlocks',\n]);\n"
    }
  },
  {
    "ui_type": "dynamic_tables",
    "title": "Detailed Breakdown",
    "description": "Three related datasets for deeper analysis",
    "data": {
      "tables": [
        {
          "table_id": "users_by_plan",
          "title": "Users by Plan",
          "columns": [
            {
              "key": "plan",
              "label": "Plan"
            },
            {
              "key": "users",
              "label": "Users"
            }
          ],
          "rows": [
            {
              "plan": "Free",
              "users": 15400
            },
            {
              "plan": "Pro",
              "users": 5200
            },
            {
              "plan": "Enterprise",
              "users": 1600
            }
          ]
        },
        {
          "table_id": "revenue_by_region",
          "title": "Revenue by Region",
          "columns": [
            {
              "key": "region",
              "label": "Region"
            },
            {
              "key": "revenue",
              "label": "Revenue ($)"
            }
          ],
          "rows": [
            {
              "region": "North America",
              "revenue": 52000
            },
            {
              "region": "Europe",
              "revenue": 39000
            },
            {
              "region": "Asia",
              "revenue": 28000
            }
          ]
        },
        {
          "table_id": "top_customers",
          "title": "Top Customers",
          "columns": [
            {
              "key": "company",
              "label": "Company"
            },
            {
              "key": "country",
              "label": "Country"
            },
            {
              "key": "spend",
              "label": "Annual Spend ($)"
            }
          ],
          "rows": [
            {
              "company": "Acme Corp",
              "country": "USA",
              "spend": 12000
            },
            {
              "company": "NordSoft",
              "country": "Germany",
              "spend": 9400
            },
            {
              "company": "ZenData",
              "country": "India",
              "spend": 8700
            }
          ]
        }
      ]
    }
  }
]
END, true),
        ],
    ],
];
