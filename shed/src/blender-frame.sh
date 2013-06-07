#!/bin/bash

# ARC Cluster Blender Job Submission
# Takes a blender file with some params
# and submits the job to the cluster

# Copyright 2013 Andrew Kester
# Released under GNU-GPL

##############################################

set -e
# Load the vars
infile=$1
frame=$2

#Build the submission script
cat <<EOS | qsub -
#!/bin/bash
#PBS -N blender-render-frame
#PBS -l nodes=1:ppn=1

blender -b $infile -t 1 -x 1 -o //out -F JPEG -s $frame -e $frame -a
EOS

