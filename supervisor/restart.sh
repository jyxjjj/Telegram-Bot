#!/bin/bash
supervisorctl stop YPBot-Queue:*
supervisorctl start YPBot-Queue:*
