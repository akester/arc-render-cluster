#!/bin/bash

# ARC Cluster Blender Job Submission
# Takes a blender file with some params
# and submits the job to the cluster

# Copyright 2013 Andrew Kester
# Released under GNU-GPL

##############################################

set -e
echo "Counting Frames..."
# Load the vars
infile=$1
startf=$2
endf=$3
priority=$4

# Set default priority
if [ "$priority" = "" ]; then
	priority=0
fi

totalframes=`expr $endf - $startf + 1`

echo "Submitting jobs to cluster..."
x=$startf
while [ $x -le $endf ]; do
	echo "Submitting frame $x"
	#Build the submission script
	cat <<EOS | qsub -
#!/bin/bash
#PBS -N blender-render-frame
#PBS -l nodes=1:ppn=1

blender -b $infile -t 1 -x 1 -o //out -F JPEG -s $x -e $x -a
EOS
	x=`expr $x + 1`
done
