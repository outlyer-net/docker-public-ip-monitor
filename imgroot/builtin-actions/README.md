"Actions" are executable files.

Built-in actions are placed in /builtin-actions, additional actions can also be added by mounting a directory to /data/actions.

They will be invoked on each IP check with the following arguments:

1. Timestamp (as seconds since the UNIX Epoch)
1. Current IP
1. Previous IP

Note an unknown IP address is indicated as 0.0.0.0.
