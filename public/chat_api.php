<?php
require_once '../app/functions.php';

header('Content-Type: application/json; charset=utf-8');

function containsWord($text, $word)
{
    return mb_strpos($text, $word) !== false;
}

function getChatGPTAdvice($message, $carsText)
{
    $apiKey = "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";

    if (!$apiKey) {
        return "API ключ отсутствует.";
    }

    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            [
                "role" => "system",
                "content" => "Ты авто-консультант. Дай короткий совет на русском языке. Максимум 3 предложения. Не выдумывай автомобили, анализируй только переданные варианты."
            ],
            [
                "role" => "user",
                "content" => "Запрос пользователя: " . $message . "\n\nПодобранные машины:\n" . $carsText
            ]
        ],
        "temperature" => 0.4,
        "max_tokens" => 180
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return "Совет ИИ временно недоступен из-за ошибки соединения.";
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if (!isset($result['choices'][0]['message']['content'])) {
        return "Совет ИИ временно недоступен.";
    }

    return trim($result['choices'][0]['message']['content']);
}

$message = mb_strtolower(trim($_POST['message'] ?? ''));

if ($message === '') {
    echo json_encode([
        "reply" => "Напиши, какую машину ты хочешь 🙂"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

global $pdo;

$stmt = $pdo->query("SELECT * FROM cars WHERE status = 'available'");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

$results = [];

foreach ($cars as $car) {
    $score = 0;
    $reasons = [];

    $title = mb_strtolower($car['title'] ?? '');
    $brand = mb_strtolower($car['brand'] ?? '');
    $model = mb_strtolower($car['model'] ?? '');
    $bodyType = mb_strtolower($car['body_type'] ?? '');
    $engineVolume = (float) ($car['engine_volume'] ?? 0);
    $description = mb_strtolower($car['description'] ?? '');

    $year = (int) ($car['year'] ?? 0);
    $mileage = (int) ($car['mileage'] ?? 0);
    $price = (float) ($car['price'] ?? 0);

    if (containsWord($message, $brand) && $brand !== '') {
        $score += 6;
        $reasons[] = "совпадает с выбранной маркой";
    }

    if (containsWord($message, $model) && $model !== '') {
        $score += 7;
        $reasons[] = "совпадает с выбранной моделью";
    }

    if (preg_match('/([1-9]\.[0-9])/', $message, $matches)) {
        $requestedEngine = (float) $matches[1];

        if ($engineVolume > 0 && abs($engineVolume - $requestedEngine) <= 0.1) {
            $score += 8;
            $reasons[] = "подходит по объёму двигателя";
        }
    }

    if (
        containsWord($message, 'эконом') ||
        containsWord($message, 'малый расход') ||
        containsWord($message, 'расход') ||
        containsWord($message, 'дешев') ||
        containsWord($message, 'недорог') ||
        containsWord($message, 'бюджет')
    ) {
        if ($price <= 12000) {
            $score += 4;
            $reasons[] = "подходит по цене";
        }

        if ($mileage <= 180000) {
            $score += 2;
            $reasons[] = "имеет приемлемый пробег";
        }

        if ($engineVolume > 0 && $engineVolume <= 1.6) {
            $score += 6;
            $reasons[] = "небольшой объём двигателя";
        }

        if (
            containsWord($description, 'эконом') ||
            containsWord($description, 'малый расход') ||
            containsWord($description, 'дизель') ||
            containsWord($description, 'hybrid') ||
            containsWord($description, 'гибрид') ||
            containsWord($model, 'prius')
        ) {
            $score += 5;
            $reasons[] = "подходит как экономичный вариант";
        }
    }

    if (
        containsWord($message, 'мощн') ||
        containsWord($message, 'быстр') ||
        containsWord($message, 'спорт') ||
        containsWord($message, 'динамич')
    ) {
        if ($engineVolume >= 2.5) {
            $score += 7;
            $reasons[] = "подходит как более мощный вариант";
        } elseif ($engineVolume >= 2.0) {
            $score += 4;
            $reasons[] = "имеет средний объём двигателя";
        }
    }

    if (
        containsWord($message, 'маленький пробег') ||
        containsWord($message, 'небольшой пробег') ||
        containsWord($message, 'малый пробег') ||
        containsWord($message, 'до 100')
    ) {
        if ($mileage <= 100000) {
            $score += 6;
            $reasons[] = "имеет небольшой пробег";
        }
    }

    if (
        containsWord($message, 'до 150') ||
        containsWord($message, 'пробег до 150')
    ) {
        if ($mileage <= 150000) {
            $score += 5;
            $reasons[] = "пробег подходит под запрос";
        }
    }

    if (
        containsWord($message, 'высок') ||
        containsWord($message, 'кроссовер') ||
        containsWord($message, 'джип') ||
        containsWord($message, 'suv') ||
        containsWord($message, 'внедорож')
    ) {
        if ($bodyType === 'suv' || $bodyType === 'crossover') {
            $score += 8;
            $reasons[] = "подходит по типу кузова";
        }

        $suvWords = [
            'x3',
            'x5',
            'x6',
            'q3',
            'q5',
            'q7',
            'touareg',
            'tiguan',
            'rav4',
            'cr-v',
            'cx-5',
            'duster',
            'sportage',
            'tucson',
            'kodiaq',
            'koleos',
            'captur',
            'suv',
            'кроссовер',
            'джип',
            'внедорожник'
        ];

        foreach ($suvWords as $word) {
            if (
                containsWord($title, $word) ||
                containsWord($model, $word) ||
                containsWord($description, $word)
            ) {
                $score += 5;
                $reasons[] = "модель относится к SUV/кроссоверам";
                break;
            }
        }
    }

    if (containsWord($message, 'седан') && $bodyType === 'sedan') {
        $score += 6;
        $reasons[] = "это седан";
    }

    if (
        (containsWord($message, 'купе') || containsWord($message, 'coupe')) &&
        $bodyType === 'coupe'
    ) {
        $score += 6;
        $reasons[] = "это купе";
    }

    if (
        (containsWord($message, 'универсал') || containsWord($message, 'семейн') || containsWord($message, 'для семьи')) &&
        ($bodyType === 'universal' || $bodyType === 'minivan' || $bodyType === 'suv' || $bodyType === 'crossover')
    ) {
        $score += 6;
        $reasons[] = "подходит для семьи";
    }

    if (
        containsWord($message, 'нов') ||
        containsWord($message, 'свеж')
    ) {
        if ($year >= 2018) {
            $score += 5;
            $reasons[] = "достаточно свежий год выпуска";
        } elseif ($year >= 2016) {
            $score += 3;
            $reasons[] = "относительно свежий год выпуска";
        }
    }

    if (
        containsWord($message, 'надеж') ||
        containsWord($message, 'без проблем')
    ) {
        if ($mileage <= 160000) {
            $score += 3;
            $reasons[] = "не слишком большой пробег";
        }

        if ($year >= 2014) {
            $score += 2;
            $reasons[] = "не слишком старый автомобиль";
        }
    }

    if ($score > 0) {
        $car['score'] = $score;
        $car['reasons'] = array_unique($reasons);
        $results[] = $car;
    }
}

usort($results, function ($a, $b) {
    return $b['score'] <=> $a['score'];
});

$results = array_slice($results, 0, 3);

if (empty($results)) {
    echo json_encode([
        "reply" => "
            <div style='background:#111;border:1px solid #800039;border-radius:14px;padding:14px;'>
                Я не нашёл подходящие варианты. Попробуй написать подробнее: бюджет, марку, год, пробег, объём двигателя или тип кузова.
            </div>
        "
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$carsText = "";

foreach ($results as $car) {
    $carsText .= $car['brand'] . " " . $car['model'];
    $carsText .= ", кузов: " . ($car['body_type'] ?: "не указан");
    $carsText .= ", двигатель: " . ($car['engine_volume'] ?: "не указан");
    $carsText .= ", год: " . $car['year'];
    $carsText .= ", пробег: " . $car['mileage'] . " км";
    $carsText .= ", цена: " . $car['price'] . " $";
    $carsText .= ", причины: " . implode(", ", array_unique($car['reasons']));
    $carsText .= "\n";
}

$advice = getChatGPTAdvice($message, $carsText);

$reply = '<div class="ai-results">';

$reply .= '<div style="
    background:#111;
    border:1px solid #333;
    border-radius:16px;
    padding:14px;
    margin-bottom:14px;
">
    <div style="color:#800039;font-weight:900;margin-bottom:6px;">💬 Совет ChatGPT</div>
    <div style="color:#ddd;line-height:1.5;">' . htmlspecialchars($advice) . '</div>
</div>';

$reply .= '<div style="margin-bottom:12px; font-weight:700;">Я подобрал для тебя подходящие варианты:</div>';

foreach ($results as $car) {
    $image = !empty($car['image']) ? htmlspecialchars($car['image']) : 'https://via.placeholder.com/300x180';

    $reply .= '<div style="
        background:#111;
        border:1px solid #800039;
        border-radius:16px;
        padding:14px;
        margin-bottom:14px;
        display:flex;
        gap:14px;
        align-items:center;
    ">';

    $reply .= '<img src="' . $image . '" style="
        width:150px;
        height:100px;
        object-fit:cover;
        border-radius:12px;
        border:1px solid #222;
    ">';

    $reply .= '<div style="flex:1;">';

    $reply .= '<div style="font-size:17px; font-weight:800; color:#fff;">
        🚗 ' . htmlspecialchars($car['brand']) . ' ' . htmlspecialchars($car['model']) . '
    </div>';

    $reply .= '<div style="color:#ccc; margin-top:4px;">
        Кузов: ' . htmlspecialchars($car['body_type'] ?: 'не указан') . ' |
        Двигатель: ' . htmlspecialchars($car['engine_volume'] ?: 'не указан') . '
    </div>';

    $reply .= '<div style="color:#ccc; margin-top:4px;">
        Год: ' . htmlspecialchars($car['year']) . ' |
        Пробег: ' . htmlspecialchars($car['mileage']) . ' км
    </div>';

    $reply .= '<div style="color:#800039; font-weight:900; margin-top:4px;">
        ' . htmlspecialchars($car['price']) . ' $
    </div>';

    if (!empty($car['reasons'])) {
        $reply .= '<div style="color:#aaa; font-size:13px; margin-top:6px;">
            Почему подходит: ' . htmlspecialchars(implode(", ", array_unique($car['reasons']))) . '.
        </div>';
    }

    $reply .= '<a href="car.php?id=' . (int) $car['id'] . '" style="
        display:inline-block;
        margin-top:10px;
        background:#800039;
        color:#fff;
        padding:8px 14px;
        border-radius:10px;
        text-decoration:none;
        font-weight:700;
    ">
        Открыть объявление
    </a>';

    $reply .= '</div>';
    $reply .= '</div>';
}

$reply .= '</div>';

echo json_encode(["reply" => $reply], JSON_UNESCAPED_UNICODE);