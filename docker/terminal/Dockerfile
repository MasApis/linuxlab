FROM ubuntu:22.04

# Hindari interaksi prompt saat apt-get
ENV DEBIAN_FRONTEND=noninteractive
ENV LANG=C.UTF-8
ENV LC_ALL=C.UTF-8

# Pasang utilitas Linux standar pembelajaran pemula (coreutils, text processing, editor)
RUN apt-get update && apt-get install -y --no-install-recommends \
    bash \
    coreutils \
    findutils \
    grep \
    sed \
    gawk \
    less \
    nano \
    vim-tiny \
    tar \
    gzip \
    procps \
    psmisc \
    file \
    && rm -rf /var/lib/apt/lists/*

# Buat user non-root 'student' (UID/GID 1000) tanpa akses sudo (SEC-TERM-03)
RUN groupadd -g 1000 student && \
    useradd -u 1000 -g student -m -s /bin/bash student

# Konfigurasi bash prompt yang ramah pemula di /home/student/.bashrc
RUN echo "export PS1='\[\033[01;32m\]student@linuxlab\[\033[00m\]:\[\033[01;34m\]\w\[\033[00m\]\$ '" >> /home/student/.bashrc && \
    echo "alias ll='ls -la --color=auto'" >> /home/student/.bashrc && \
    echo "alias ls='ls --color=auto'" >> /home/student/.bashrc && \
    chown -R student:student /home/student

USER student
WORKDIR /home/student

CMD ["/bin/bash"]
