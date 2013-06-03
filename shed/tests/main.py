#!/usr/bin/env python

import sys
sys.path.append('/home/andrew/projects/arc-render-cluster/shed/src')

import MySQLdb
import shed
import unittest

class shed_SSHFunctions(unittest.TestCase):
    def setUp(self):
        self.s = shed.shed()
        
    def testSSHConnection(self):
        result = self.s.sshCommand('render-01', 'uname -a')
        uname = "Linux render-01 3.2.0-4-amd64 #1 SMP Debian 3.2.41-2+deb7u2 x86_64 GNU/Linux\n"
        self.assertEqual(result, uname)
        
class shed_MainFunctions(unittest.TestCase):
    def setUp(self):
        self.s = shed.shed()
    
    def testLibrary(self):
        self.assertIsInstance(self.s, shed.shed)
        
    def testMySQL(self):
        self.s.connectMySQL()
        self.assertIsInstance(self.s.db, MySQLdb.cursors.Cursor)
        self.s.closeMySQL()
        
class shed_DBFunctions(unittest.TestCase):
    def setUp(self):
        self.s = shed.shed()
    
    def testConnection(self):
        self.s.connectMySQL()
        self.assertIsInstance(self.s.db, MySQLdb.cursors.Cursor)
        self.s.closeMySQL()
    
    def testFetchJobs(self):
        self.s.connectMySQL()
        print self.s.getJobs(0)
        self.s.closeMySQL()
        
# Begin Testing
if __name__ == '__main__':
    unittest.main()