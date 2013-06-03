#!/usr/bin/env python

import sys
sys.path.append('/home/andrew/projects/arc-render-cluster/shed/src')

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
        
        
# Begin Testing
if __name__ == '__main__':
    unittest.main()