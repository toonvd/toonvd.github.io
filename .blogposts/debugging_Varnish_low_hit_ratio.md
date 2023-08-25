## Debugging a low Varnish hit ratio
When using full page cache, you want to have your hit ratio as high as possible.
Sometimes it is hard to find out why your hit rate is low.
This post describes some options to help you on your way.

### Step 1: check varnishstat
- Check `varnishstat` for the bans counter if the average is high, proceed to step 5.
- Check `varnishstat` for nuked objects, if the amount is high, you probably have insufficient allocated memory.
This means that you either assigned to little, or cache very inefficiently. Use common sense to determine which one it is, your available memory is not infinite. 

### Step 2: logging
The first thing I usually start with, is logging misses to a file.\
An ideal moment to do this, is after your site is warmed by a cache warmer and on a peak moment.
```bash
varnishlog -q "VCL_call eq MISS" > misses.log
```

### Step 3: finding patterns
You can often find patterns in these misses.
For example specific types of product or category pages in Magento.\
This is the first thing to look for.

### Step 4: check hashes
When patterns are found, you can often find variable hashes.
These hashes are caused by headers or url param that are not stripped.
The <a href="https://github.com/mage-os/mageos-magento2/blob/631315d7243593023330cfa93b9710f0422b2e68/app/code/Magento/PageCache/etc/varnish6.vcl#L100">Magento VCL</a> has specific rules to strip marketing params, you can add found params there.

### Step 5: log flushes
If to many flushes happen, you should start by logging the flushes.
After logging them, you can optimize code.
```bash
varnishlog -g request -q 'ReqMethod eq "PURGE"' > purges.log
```