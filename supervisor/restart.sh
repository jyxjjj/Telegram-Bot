#!/bin/bash
supervisorctl stop SearchBot-Queue:*
supervisorctl start SearchBot-Queue:*
