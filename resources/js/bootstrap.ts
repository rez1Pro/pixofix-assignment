import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


// Check if the browser supports notifications
if ("Notification" in window) {
    // Check the current permission status
    if (Notification.permission === "granted") {
        // If permission is already granted, show a notification
        new Notification("Hello! Notifications are enabled.");
    } else if (Notification.permission !== "denied") {
        // If permission is not denied, request permission
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                // If the user grants permission, show a notification
                new Notification("Thank you for enabling notifications!");
            } else {
                console.log("User denied notification permission.");
            }
        });
    } else {
        console.log("Notifications are blocked. Cannot show notifications.");
    }
} else {
    console.log("This browser does not support notifications.");
}