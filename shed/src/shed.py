#!/usr/bin/env python

import ConfigParser
import errno
import paramiko
import sys

class shed():
    # System Specific Settings
    configFileLocation= '../cfg/shed.conf'
    
    def __init__(self):
        self.config = self.parseConfig()

    def parseConfig(self):
        # Read the configuration file
        try:
            config = ConfigParser.SafeConfigParser()
            config.read(self.configFileLocation)
        except ConfigParser.Error as e:
            sys.stdout.write('Error parsing configuration.  Returned: {0}\n'
                             .format(e))
            sys.stdout.flush()
            exit(-errno.EIO)

        # Build the file into a dict that we can use
        out = {}
        for section in config.sections():
            out[section] = {}
            for option in config.options(section):
                out[section][option] = config.get(section, option)

        return out