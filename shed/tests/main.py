#!/usr/bin/env python

import sys
sys.path.append('/home/andrew/projects/arc-render-cluster/shed/src')

import shed
import unittest

class shed_SSHFunctions(unittest.TestCase):
    def setUp(self):
        self.s = shed.shed()
        
    def testSSHConnection(self):
        self.s.sshCommand('render-01', 'uname -a')
        
# Begin Testing
if __name__ == '__main__':
    unittest.main()