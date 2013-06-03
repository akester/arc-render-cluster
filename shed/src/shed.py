#!/usr/bin/env python

import ConfigParser
import errno
import MySQLdb
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

    def connectMySQL(self):
        self.con = MySQLdb.connect(self.config['mysql']['host'],
                              self.config['mysql']['username'],
                              self.config['mysql']['password'],
                              self.config['mysql']['database'])
        self.db = self.con.cursor()
        return True

    def closeMySQL(self):
        self.con.close()

    def sshConnect(self, host):
        client = paramiko.SSHClient()

        client.set_missing_host_key_policy(paramiko.WarningPolicy())
        client.load_system_host_keys()

        if not self.config[host]['address']:
            raise KeyError('E: Missing address for host {0}'.format(host))

        client.connect(self.config[host]['address'],
                       username=self.config[host]['user'],
                       key_filename=self.config[host]['keyfile'])

        return client

    def sshClose(self, client):
        client.close()

    def sshCommand(self, host, command):
        client = self.sshConnect(host)
        stdin, stdout, stderr = client.exec_command(command)
        
        stdoutStr = stdout.read()
        stderrStr = stderr.read()

        self.sshClose(client)

        if stderrStr != "":
            raise RuntimeError('E: SSH Command returned data on stderr: {0}'
                               .format(stderrStr))
            
        return stdoutStr