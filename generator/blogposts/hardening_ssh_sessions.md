## Hardening ssh sessions
Depending on the situation, it might be needed to limit what a specific ssh key can do.\
Possible wishes can be locking IP, preventing port forwarding and even lock possible commands. All of this, can be done using `authorized_keys`.

For this example, we are going to allow an owner of a specific key to only run Docker commands.
This can be used to limit actions that a CI tool can do.

Below are the steps:

### Editing authorized_keys
```bash
from="XXX.XXX.XXX.XXX/32",no-port-forwarding,no-X11-forwarding,no-pty,command="/home/$USER/bin/restrict-ssh" ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDNHbNUwhdEyISHE3Hogwm1UX9SZRXVxbRq+95SzDO2BWDK5H47IAldX/t3bx8aXyUKEQmvVyAVNTS15bRD72KfJsADTqgXyr5Dv+SSwYFkquNbJxWgNNX2qx1BIU7F9vuUUBYn6kAGJ3JWI6jBpAb8RqSlbuDsaGmY633Pi5bmkT/+a8hSCLZwumtrziTm3gBeJMq0mzEPeC1AmH8a/ICcsaiVg5ywm6Z2WKGe/aKO1IIkMvhZhgMHo9jJrmIVOCo4gREVBPy8cH2Ec8pkCP/LePJTkQTplrGe8j+TzElYwPR74PiC9RbB8HpA3M7IRonTRMeYsFPRnFAZLdquRF6zQHcVpISkMrsKMAUTa9sljSM8V6FTydfD9IdqndwnDcGpYj/xio/wS7efjaRO4fFTD7Sxzio7cAqNRrZOpgztSdh0mydjfvr9ZQAVWNGjiPVXAAM1UNAGg/qQiS8LWfGdXBMCBap/oCE3m77FykHuB8gttCOSFmiPInxJMAAr+/aXb3hKX4g/6ioR1a78ah8oSxXQADZmGzyQYE/xEZ0KqnAm6jPhntA3zx1CcEIIa+CSF27p/LI7OtPwmGv0rT5QguKGekFUpLNJMCD8Et69vxVOAQCmqkNEmgTJYDE3qAAqkfigPoXZxqpw29IYri2QRKH7bF3YpB0q03udb95cTQ== toonvd 
```
Above entry in `authorized_keys` limits ip, prevents port and x11 forwarding and triggers a script each time the session is opened.
This script will receive ssh commands and will validate them.

### Creating a bash script
```bash
#!/bin/bash -fue
case $SSH_ORIGINAL_COMMAND in
"docker system dial-stdio") exec $SSH_ORIGINAL_COMMAND ;;
*) echo Access denied ;;
esac
```
Above script only allows the session to run docker commands.
All other communication ends with a simple `Access denied` echo.