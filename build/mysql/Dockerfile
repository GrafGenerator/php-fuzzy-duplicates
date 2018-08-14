FROM mysql:5.6

ENV MYSQL_DATABASE FuzzyDuplicates
ENV MYSQL_ROOT_PASSWORD RootPassword123
ENV MYSQL_USER FuzzerUser
ENV MYSQL_PASSWORD FuzzerPassword123

ENV TZ=Asia/Novosibirsk
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -my \
  curl \
  wget \
  git \
  zip \
  gcc \
  g++ \
  make \
  libmysqlclient-dev \
  autoconf \
  build-essential \
  libtool

COPY ext/ssdeep.sh /tmp/ssdeep.sh
RUN chmod +x /tmp/ssdeep.sh
RUN /tmp/ssdeep.sh