const schemes = [
    "Schemes", // English
    "ಯೋಜನೆಗಳು", // Kannada
    "திட்டங்கள்", // Tamil
    "పథకాలు" // Telugu
];

const descriptions = [
    "This website helps you find a list of government schemes currently available from state and central governments of India. It is a platform where you can find all government schemes in one place.", // English
    "ಈ ವೆಬ್‌ಸೈಟ್ ನೀವು ಕರ್ನಾಟಕ ಮತ್ತು ಕೇಂದ್ರ ಸರ್ಕಾರಗಳಿಂದ ಪ್ರಸ್ತುತ ಲಭ್ಯವಿರುವ ಸರ್ಕಾರದ ಯೋಜನೆಗಳ ಪಟ್ಟಿ ಕಂಡುಹಿಡಿಯಲು ಸಹಾಯ ಮಾಡುತ್ತದೆ. ಇದು ನೀವು ಎಲ್ಲಾ ಸರ್ಕಾರದ ಯೋಜನೆಗಳನ್ನು ಒಂದೇ ಸ್ಥಳದಲ್ಲಿ ಕಂಡುಹಿಡಿಯಬಹುದಾದ ವೇದಿಕೆ.", // Kannada
    "இந்த இணையதளம் இந்தியாவின் மாநில மற்றும் மைய அரசுகள் வழங்கும் தற்போதைய அரசு திட்டங்களின் பட்டியலை கண்டுபிடிப்பதில் உங்களுக்கு உதவும். இது நீங்கள் அனைத்து அரசு திட்டங்களையும் ஒரே இடத்தில் கண்டுபிடிக்கக்கூடிய தளம்.", // Tamil
    "ఈ వెబ్‌సైట్ మీరు తెలంగాణ మరియు కేంద్ర ప్రభుత్వాలు అందిస్తున్న ప్రస్తుత ప్రభుత్వ పథకాలను కనుగొనడంలో మీకు సహాయం చేస్తుంది. ఇది మీరు అన్ని ప్రభుత్వ పథకాలను ఒకే చోట కనుగొనగల వేదిక." // Telugu
];

let currentIndex = 0;
const schemeWordElement = document.getElementById('scheme-word');
const descriptionElement = document.getElementById('description-text');

setInterval(() => {
    // Change the description text
    descriptionElement.style.opacity = 0;
    setTimeout(() => {
        descriptionElement.textContent = descriptions[currentIndex];
        descriptionElement.style.opacity = 1;
    }, 500); // Delay before changing description

    // Change the scheme word text
    schemeWordElement.style.opacity = 0;
    setTimeout(() => {
        schemeWordElement.textContent = schemes[currentIndex];
        schemeWordElement.style.opacity = 1;
    }, 500); // Delay before changing scheme word

    currentIndex = (currentIndex + 1) % schemes.length; // Cycle through languages
}, 10000); // Change every 10 seconds