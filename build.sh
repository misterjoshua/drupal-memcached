#!/bin/bash

function log() { echo "$*" >&2; }
function die() { log "$*"; exit 1; }

HERE=${HERE:-$(dirname $0)}
BUILD_DIR=${BUILD_DIR:-$HERE/build}

log "Initializing helm on the client-side"
helm init --client-only >/dev/null

if [ ! -d "$BUILD_DIR" ]; then
    log "Creating build dir at $BUILD_DIR"
    mkdir $BUILD_DIR
fi

log "Creating helm package"
helm package . -d $BUILD_DIR