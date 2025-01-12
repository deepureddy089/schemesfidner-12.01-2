<?php
// ai_openapi_handle.php

// Start the session
include('ai_session_start.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection file
include('db_ai_login_connection.php');

// Get user query from the search form
$userQuery = $_POST['query'];

// Fetch all schemes from the database
$sql = "SELECT scheme_name, state, age_group, state_logo, scheme_link FROM schemes";
$result = $conn->query($sql);

$schemesData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schemesData[] = $row;
    }
}

// Remove duplicates from the database array
$schemesData = array_map("unserialize", array_unique(array_map("serialize", $schemesData)));

// Prepare the data for the ChatGPT API (for database results)
$apiKey = "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"; // Replace with your OpenAI API key
$apiUrl = "https://api.openai.com/v1/chat/completions";

$systemPrompt = "You are a helpful assistant that provides information about government schemes. The user will ask about specific scheme names, states, age groups, or a combination of these. Your task is to search the following database and return only the schemes that match the user's query.

Database (in JSON format):
" . json_encode($schemesData) . "

Instructions:
1. **City-to-State Mapping**:
   - If the user provides a city name (e.g., 'Chennai', 'Bangalore', 'Vizag'), use your general knowledge to map the city to its corresponding state (e.g., 'Chennai' → 'Tamil Nadu', 'Bangalore' → 'Karnataka', 'Vizag' → 'Andhra Pradesh').
   - Once the state is identified, search the database for schemes in that state.

2. **State Matching**:
   - If the user provides a full or partial state name (e.g., 'Tamil Nadu', 'TN', 'Tamil'), match it to the correct state in the database.
   - If the user mentions 'residents of [city/state]' or 'people located in [city/state]', treat it as a query for schemes in that state.

3. **Age Group Interpretation**:
   - Interpret the 'age_group' column dynamically:
     - '0-18' means the scheme is eligible for anyone aged 0 to 18.
     - '19-60' means the scheme is eligible for anyone aged 19 to 60.
     - '60+' means the scheme is eligible for anyone aged 60 and above.
   - For example, if the user asks for 'schemes for 15-year-olds', return schemes with an 'age_group' of '0-18'.

4. **Keyword and Synonym Matching**:
   - If the user asks for schemes related to 'uneducated', interpret this as schemes that might benefit people without formal education (e.g., skill development, vocational training, or schemes for graduates).
   - Use synonyms and related terms to expand the search. For example:
     - 'Uneducated' → 'Graduates', 'Skill Development', 'Vocational Training'.
     - 'Pension' → 'Retirement Benefits', 'Old Age Support'.
   - Match even a single keyword in the user's query to the scheme name or age group

5. **Response Format**:
   - Format the response as a valid JSON array of objects, where each object represents a scheme and has the following keys: scheme_name, state, age_group, and scheme_link.
   - Provide a maximum of 8 results.
   - If no schemes match the query, return an empty array.

User Query:
" . $userQuery;

$dataDatabase = [
    "model" => "gpt-3.5-turbo", // Use GPT-3.5 Turbo
    "messages" => [
        [
            "role" => "system",
            "content" => $systemPrompt
        ],
        [
            "role" => "user",
            "content" => $userQuery
        ]
    ],
    "max_tokens" => 4000 // Increased tokens for detailed responses
];

$chDatabase = curl_init($apiUrl);
curl_setopt($chDatabase, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);
curl_setopt($chDatabase, CURLOPT_POST, 1);
curl_setopt($chDatabase, CURLOPT_POSTFIELDS, json_encode($dataDatabase));
curl_setopt($chDatabase, CURLOPT_RETURNTRANSFER, true);

$responseDatabase = curl_exec($chDatabase);
if (curl_errno($chDatabase)) {
    $_SESSION['error'] = "Error calling OpenAI API for database results: " . curl_error($chDatabase);
    header("Location: ai_search-form.php");
    exit;
}
curl_close($chDatabase);

// Debugging: Log the raw API response
error_log("Raw Database API Response: " . $responseDatabase);

// Decode the database response
$responseDataDatabase = json_decode($responseDatabase, true);
if (json_last_error() === JSON_ERROR_NONE && isset($responseDataDatabase['choices'][0]['message']['content'])) {
    $aiResponseDatabase = $responseDataDatabase['choices'][0]['message']['content'];
    error_log("AI Response for Database: " . $aiResponseDatabase); // Log the AI response

    // Remove Markdown code block (if present)
    $aiResponseDatabase = str_replace('```json', '', $aiResponseDatabase);
    $aiResponseDatabase = str_replace('```', '', $aiResponseDatabase);

    // Decode the cleaned response
    $matchedSchemes = json_decode($aiResponseDatabase, true);

    if (json_last_error() !== JSON_ERROR_NONE || empty($matchedSchemes)) {
        error_log("Failed to decode AI response or no schemes matched.");
        $matchedSchemes = []; // Fallback if decoding fails or results are empty
    }
} else {
    error_log("Invalid API response for database results.");
    $matchedSchemes = []; // Fallback if API response is invalid
}

// Generate a 60-80 word summary based on the matched schemes
if (!empty($matchedSchemes)) {
    // Extract unique states
    $states = array_unique(array_column($matchedSchemes, 'state'));

    // Build the summary
    $summary = "Based on your search query '$userQuery', we found " . count($matchedSchemes) . " relevant government schemes in our database. These schemes are offered in the following states: " . implode(", ", $states) . ". ";
    $summary .= "These schemes are tailored to match your query and provide the most relevant information. Below are the schemes:";
} else {
    $summary = "Based on your search query '$userQuery', no relevant schemes were found in our database.";
}

// Store the matched schemes and summary in the session
$_SESSION['ai_results_database'] = $matchedSchemes;
$_SESSION['descriptive_answer'] = $summary;

// Prepare the data for the ChatGPT API (for web-based results)
$dataWeb = [
    "model" => "gpt-3.5-turbo", // Use GPT-4 Turbo
    "messages" => [
        [
            "role" => "system",
            "content" => "You are a helpful assistant that provides information about government schemes. The user will ask about specific schemes, and you must provide accurate and relevant results based on general knowledge. Format the response as a valid JSON array of objects, where each object represents a scheme and has the following keys: scheme_name, state, age_group, and link. Limit the response to a maximum of 6 schemes. Ensure the results are strictly based on the user's query: '$userQuery'. If the query contains a state name, prioritize schemes from that state. Search web for schemes related to the state user entered, age group user entered. If no relevant schemes are found, return an empty array."
        ],
        [
            "role" => "user",
            "content" => $userQuery
        ]
    ],
    "max_tokens" => 4000 // Increased tokens for detailed responses
];

$chWeb = curl_init($apiUrl);
curl_setopt($chWeb, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);
curl_setopt($chWeb, CURLOPT_POST, 1);
curl_setopt($chWeb, CURLOPT_POSTFIELDS, json_encode($dataWeb));
curl_setopt($chWeb, CURLOPT_RETURNTRANSFER, true);

$responseWeb = curl_exec($chWeb);
if (curl_errno($chWeb)) {
    $_SESSION['error'] = "Error calling OpenAI API for web results: " . curl_error($chWeb);
    header("Location: ai_search-form.php");
    exit;
}
curl_close($chWeb);

// Decode the web-based response
$responseDataWeb = json_decode($responseWeb, true);
if (json_last_error() === JSON_ERROR_NONE && isset($responseDataWeb['choices'][0]['message']['content'])) {
    $aiResponseWeb = $responseDataWeb['choices'][0]['message']['content'];
    $schemesWeb = json_decode($aiResponseWeb, true);

    if (json_last_error() !== JSON_ERROR_NONE || empty($schemesWeb)) {
        $schemesWeb = []; // Fallback if decoding fails or results are empty
    }
} else {
    $schemesWeb = []; // Fallback if API response is invalid
}

// Store the web-based results in the session
$_SESSION['ai_results_web'] = $schemesWeb;

// Redirect back to the search form
header("Location: ai_search-form.php");
exit;
?>