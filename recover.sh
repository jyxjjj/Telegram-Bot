#!/bin/bash

#FILES=$(git log --diff-filter=D --summary | grep -i -E 'delete mode [0-9]+ .*bilibili' | awk '{print $4}')

#for FILE in $FILES; do
#    LATEST_COMMIT=$(git log --diff-filter=D --pretty=format:%H -- "$FILE" | head -n 1)
#    if [ -n "$LATEST_COMMIT" ]; then
#        git checkout "$LATEST_COMMIT"^ -- "$FILE"
#        echo "from $LATEST_COMMIT recover $FILE"
#    fi
#done

#FILES=$(git log --diff-filter=D --summary | grep -i -E 'delete mode [0-9]+ .*ChannelCommandHandle.*' | awk '{print $4}')
#for FILE in $FILES; do
#    LATEST_COMMIT=$(git log --diff-filter=D --pretty=format:%H -- "$FILE" | head -n 1)
#    if [ -n "$LATEST_COMMIT" ]; then
#        git checkout "$LATEST_COMMIT"^ -- "$FILE"
#        echo "from $LATEST_COMMIT recover $FILE"
#    fi
#done

#FILES=$(git log --diff-filter=D --summary | grep -i -E 'delete mode [0-9]+ .*ChannelPostHandle.*' | awk '{print $4}')
#for FILE in $FILES; do
#    LATEST_COMMIT=$(git log --diff-filter=D --pretty=format:%H -- "$FILE" | head -n 1)
#    if [ -n "$LATEST_COMMIT" ]; then
#        git checkout "$LATEST_COMMIT"^ -- "$FILE"
#        echo "from $LATEST_COMMIT recover $FILE"
#    fi
#done
