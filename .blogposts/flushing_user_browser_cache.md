## Flushing user browser cache
The other day, I received an emergency call from a customer.
This customer switched from a PWA to a regular theme but there were still workers and files in browser cache.

In this article, I will describe steps to a swift (possible) resolution.\
Since browser cache is not something that can easily be manipulated from the server,\
you might have to get inventive.

### Step 1: check if the customer has Google Tag manager
You can inject javascript that can flush workers and caches.
```javascript
(function(){
    localStorage.clear();
    sessionStorage.clear();
    navigator.serviceWorker.getRegistrations().then(function(registrations) {
        for(let registration of registrations) {
            registration.unregister();
        }
    }); 
}())
```

### Step 2: check the servers for any requests that come through
Filter access logs by an IP that has the problem and try to find requests that always hit the server.

### Step 3: first (quick) fix
Add a `clear-site-data` header on the paths that come through to the server:
```
Clear-Site-Data: '"cache","storage"'
```
Beware, this header is not yet processed by <a href="https://caniuse.com/?search=clear-site-data" target="_blank">Safari</a>!
This header also clears localstorage, make sure you don't use it on a link that your new theme uses!

### Step 4: try to find a JS file
It is possible to put in a drop in replacement for this JS file containing the JS in step 1.
