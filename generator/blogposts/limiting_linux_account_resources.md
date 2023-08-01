## Limiting linux account resources
For shared servers running Ubuntu, I was tasked to find a way to lock in resource usage specific user accounts.
After a bit of research, I ended up taking a look at `control groups` aka `cgroups`.

Control groups collect information from the kernel and allow us to take action based on this information.
Using `subtree control`, we can configure what should be collected.\
Example:
```bash
cat /sys/fs/cgroup/cgroup.subtree_control
io memory pids
```
To limit memory for example, we can write to a file called `memory.max`.\
Example:
```bash
cat /sys/fs/cgroup/user.slice/user-1001.slice/memory.max
max
```
#### Making it simple
Since Ubuntu comes with `systemd`, and `systemd` has builtin `cgroups` management, I decided to take a closer look.
As you know, in a `systemd` based system, every user runs in its own slice.
We will take advantage of this to limit its resources.

First, we will create a file for the user ID we want to limit.
```bash
vim /etc/systemd/system/user-${UUID}.slice.d/limit-user.conf
```
In this file, we will decide what to limit. In this example, we will limit the memory.
```bash
[Slice]
MemoryAccounting=yes
MemoryMax=2048M
```
`MemoryAccounting` makes sure collection of memory data starts.
`MemoryMax` limits the memory.
After adding the file, don't forget to restart the user slice (and the user from `Ubuntu 20.04` and up).

### Pitfalls
#### MemoryMax
Reaching `MemoryMax`, will cause your system to start swapping.
If your system has swap enabled, please limit the swap also using `MemorySwapMax`.

### Limiting disk i/o does not seem to work
In this case, you are probably using `cgroups v1`.
You can check this by trying to find a `v2` mount:
```bash
findmnt --noheadings -t cgroup2
```
If the result is negative, you can enable `unified cgroups` (aka `cgroups v2`) by adding the following to your grub config:
```bash
systemd.unified_cgroup_hierarchy=1
```
Why does limiting disk i/o not work with `cgroups v1`?\
It does work, but only with direct writes.
The reason for this, is that each group is separated in `v1`.
This means that the disk group does not know anything about the others.
Enter buffers and cache:
Disk writes have to pass several layers of cache and buffers.
These layers are controlled by BLKIO and RAM.
Since `cgroups v1` separates these, it is impossible to measure i/o on these layers.