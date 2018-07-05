<?php
/*
 * Removes all entries from the kernel TCP state table that are established
 * flows to port 3890, the slapd instance. In conjunction with our iptables
 * rules, this causes those connections to be dropped.
 *
 * This is used in reconnect tests.
 */
system('/usr/bin/sudo /usr/sbin/conntrack -D -p tcp --state ESTABLISHED --dport 3890 2>&1');
