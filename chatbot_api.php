<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Chỉ hỗ trợ POST request']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user_message = $input['message'] ?? '';

if (empty($user_message)) {
    echo json_encode(['error' => 'Tin nhắn không được để trống']);
    exit;
}

$api_key = "YOUR_GEMINI_API_KEY_HERE";
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent";

// System instruction setup
$system_instruction = "Bạn là nhân viên tư vấn khách hàng của thương hiệu thời trang cao cấp L'AURA. Tên bạn là Laura. Hãy trả lời thân thiện, lịch sự, chuyên nghiệp, ngắn gọn bằng tiếng Việt. Nếu khách hỏi thông tin bạn không rõ (chẳng hạn như đơn hàng cụ thể), hãy khuyên khách đăng nhập để kiểm tra hoặc liên hệ số hotline.";

$data = [
    "systemInstruction" => [
        "parts" => [
            ["text" => $system_instruction]
        ]
    ],
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $user_message]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 800
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix SSL for local XAMPP
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-goog-api-key: ' . $api_key
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode == 200) {
    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $bot_text = $result['candidates'][0]['content']['parts'][0]['text'];
        echo json_encode(['reply' => $bot_text]);
    } else {
        echo json_encode(['reply' => "Xin lỗi, mình đang gặp sự cố kết nối."]);
    }
} else {
    echo json_encode(['reply' => "Xin lỗi, hệ thống đang bận. Vui lòng thử lại sau.", "debug" => $response]);
}
?>
